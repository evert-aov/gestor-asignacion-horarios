/**
 * Voice Recognition Module for Dynamic Reports
 * Uses Web Speech API to capture voice commands in Spanish
 * and parse them to auto-populate report form
 */

class VoiceReportGenerator {
    constructor() {
        this.recognition = null;
        this.isListening = false;
        this.transcript = '';
        this.lang = 'es-ES'; // Spanish

        // Table mappings (plural/singular forms in Spanish)
        this.tableMappings = {
            'usuarios': 'users',
            'usuario': 'users',
            'roles': 'roles',
            'rol': 'roles',
            'permisos': 'permissions',
            'permiso': 'permissions',
            'materias': 'subjects',
            'materia': 'subjects',
            'aulas': 'classrooms',
            'aula': 'classrooms',
            'grupos': 'groups',
            'grupo': 'groups',
            'asignaciones': 'assignments',
            'asignación': 'assignments',
            'horarios': 'schedules',
            'horario': 'schedules',
            'días': 'days',
            'día': 'days',
            'horarios por día': 'day_schedules',
            'gestión académica': 'academic_management',
            'gestion academica': 'academic_management',
            'periodos': 'academic_management',
            'periodo': 'academic_management',
            'carreras': 'university_careers',
            'carrera': 'university_careers',
            'asistencia': 'attendance_records',
            'asistencias': 'attendance_records',
            'auditoría': 'audit_logs',
            'auditoria': 'audit_logs',
            'bitácora': 'audit_logs',
            'bitacora': 'audit_logs',
            'reservas': 'special_reservations',
            'reserva': 'special_reservations',
            'tokens': 'qr_tokens',
            'token': 'qr_tokens',
            'notificaciones': 'notifications',
            'notificación': 'notifications'
        };

        // Common field synonyms in Spanish
        this.fieldSynonyms = {
            'id': ['id'],
            'nombre': ['name', 'nombre'],
            'nombres': ['name', 'nombre'],
            'apellido': ['last_name', 'apellido'],
            'apellidos': ['last_name', 'apellido'],
            'correo': ['email', 'correo'],
            'email': ['email', 'correo'],
            'teléfono': ['phone', 'teléfono'],
            'telefono': ['phone', 'teléfono'],
            'dirección': ['address', 'dirección'],
            'direccion': ['address', 'dirección'],
            'precio': ['price', 'sale_price', 'unit_price'],
            'precios': ['price', 'sale_price', 'unit_price'],
            'stock': ['stock'],
            'cantidad': ['quantity', 'stock'],
            'total': ['total'],
            'subtotal': ['subtotal'],
            'descuento': ['discount'],
            'fecha': ['created_at', 'date', 'invoice_date', 'expiration_date'],
            'estado': ['status'],
            'activo': ['is_active', 'active'],
            'descripción': ['description'],
            'descripcion': ['description']
        };

        this.init();
    }

    init() {
        // Check browser support
        if (!('webkitSpeechRecognition' in window) && !('SpeechRecognition' in window)) {
            console.error('Web Speech API not supported');
            return false;
        }

        // Initialize Speech Recognition
        const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
        this.recognition = new SpeechRecognition();

        this.recognition.lang = this.lang;
        this.recognition.continuous = false;
        this.recognition.interimResults = true;
        this.recognition.maxAlternatives = 1;

        this.setupEventListeners();
        return true;
    }

    setupEventListeners() {
        this.recognition.onstart = () => {
            this.isListening = true;
            this.onStart();
        };

        this.recognition.onresult = (event) => {
            let interimTranscript = '';
            let finalTranscript = '';

            for (let i = event.resultIndex; i < event.results.length; i++) {
                const transcript = event.results[i][0].transcript;
                if (event.results[i].isFinal) {
                    finalTranscript += transcript + ' ';
                } else {
                    interimTranscript += transcript;
                }
            }

            this.transcript = finalTranscript || interimTranscript;
            this.onTranscript(this.transcript, !finalTranscript);
        };

        this.recognition.onerror = (event) => {
            console.error('Speech recognition error:', event.error);
            this.isListening = false;
            this.onError(event.error);
        };

        this.recognition.onend = () => {
            this.isListening = false;
            this.onEnd();
        };
    }

    start() {
        if (this.isListening) return;

        try {
            this.transcript = '';
            this.recognition.start();
        } catch (error) {
            console.error('Error starting recognition:', error);
            this.onError('start_error');
        }
    }

    stop() {
        if (!this.isListening) return;
        this.recognition.stop();
    }

    /**
     * Parse voice command to extract table, fields, and filters
     * @param {string} command - Voice command in Spanish
     * @returns {Object} - Parsed command with table, fields, filters
     */
    parseCommand(command) {
        const lowerCommand = command.toLowerCase();
        const result = {
            table: null,
            fields: [],
            filters: [],
            raw: command
        };

        // 1. Extract table name
        result.table = this.extractTable(lowerCommand);

        // 2. Extract fields
        result.fields = this.extractFields(lowerCommand);

        // 3. Extract filters
        result.filters = this.extractFilters(lowerCommand);

        return result;
    }

    extractTable(command) {
        // Common patterns: "reporte de X", "dame X", "mostrar X", "lista de X"
        const patterns = [
            /reporte\s+de\s+(\w+)/,
            /dame\s+(?:los?|las?)\s*(\w+)/,
            /mostrar\s+(?:los?|las?)\s*(\w+)/,
            /lista\s+de\s+(\w+)/,
            /ver\s+(?:los?|las?)\s*(\w+)/,
            /quiero\s+(?:los?|las?)\s*(\w+)/
        ];

        for (const pattern of patterns) {
            const match = command.match(pattern);
            if (match && match[1]) {
                const tableName = match[1];
                // Check if it's a known table
                if (this.tableMappings[tableName]) {
                    return this.tableMappings[tableName];
                }
            }
        }

        // Fallback: check for any table keyword in the command
        for (const [spanish, english] of Object.entries(this.tableMappings)) {
            if (command.includes(spanish)) {
                return english;
            }
        }

        return null;
    }

    extractFields(command) {
        const fields = new Set();

        // Common patterns: "con X", "con X y Y", "mostrando X"
        // Stop before filter keywords like "mayor", "menor", etc.
        const withPattern = /con\s+([\w\s,y]+?)(?:\s+(?:donde|mayor|menor|igual|sea|es|contenga|y\s+su|y\s+el)|\s+del|\s+en|$)/;
        const match = command.match(withPattern);

        if (match && match[1]) {
            // Split by "y" or ","
            const fieldParts = match[1].split(/\s+y\s+|,\s*/);

            fieldParts.forEach(part => {
                const cleanPart = part.trim()
                    .replace(/^(su|el|la|los|las)\s+/, ''); // Remove articles

                // Skip if it looks like a filter keyword
                if (['mayor', 'menor', 'igual', 'contenga', 'contiene'].includes(cleanPart)) {
                    return;
                }

                // Try to find field mapping
                for (const [synonym, actualFields] of Object.entries(this.fieldSynonyms)) {
                    if (cleanPart.includes(synonym)) {
                        actualFields.forEach(f => fields.add(f));
                    }
                }
            });
        }

        return Array.from(fields);
    }

    extractFilters(command) {
        const filters = [];

        // Pattern 1: "donde X [operador] Y"
        // Examples: "donde stock menor que 10", "donde estado sea activo"
        const whereMatch = command.match(/donde\s+(.+?)(?:\s+y\s+donde|$)/);

        if (whereMatch) {
            const conditions = whereMatch[1].split(/\s+y\s+/);
            conditions.forEach(condition => {
                const filter = this.parseFilterCondition(condition);
                if (filter) {
                    filters.push(filter);
                }
            });
        }

        // Pattern 2: "y su [campo] [operador] [valor]" or "y [campo] [operador] [valor]"
        // Examples: "y su ID mayor que 5", "y el stock menor que 10"
        const afterFieldsPattern = /y\s+(?:su|el|la|los|las)?\s*([a-záéíóúñA-Z]+)\s+(mayor|menor|igual|contenga|contiene|sea|es|diferente)\s+(?:que|a|de)?\s*([^\s,]+)/gi;
        let afterFieldsMatch;

        while ((afterFieldsMatch = afterFieldsPattern.exec(command)) !== null) {
            const fieldName = afterFieldsMatch[1].trim().toLowerCase(); // Convert to lowercase for matching
            const operatorWord = afterFieldsMatch[2].trim();
            const value = afterFieldsMatch[3].trim();

            // Find the field
            let field = null;
            for (const [synonym, actualFields] of Object.entries(this.fieldSynonyms)) {
                if (fieldName.includes(synonym) || synonym.includes(fieldName) || fieldName === synonym) {
                    field = actualFields[0];
                    break;
                }
            }

            if (field) {
                // Map operator
                const operatorMap = {
                    'mayor': '>',
                    'menor': '<',
                    'igual': '=',
                    'sea': '=',
                    'es': '=',
                    'contenga': 'like',
                    'contiene': 'like',
                    'diferente': '!='
                };

                const operator = operatorMap[operatorWord] || '=';

                // Clean value
                let cleanValue = value.replace(/[,.]$/, ''); // Remove trailing punctuation
                cleanValue = cleanValue.replace('activo', '1')
                    .replace('inactivo', '0')
                    .replace('verdadero', '1')
                    .replace('falso', '0')
                    .replace('sí', '1')
                    .replace('si', '1')
                    .replace('no', '0');

                filters.push({ field, operator, value: cleanValue });
            }
        }

        // Pattern 3: Detect standalone filter at end (without "y" or "donde")
        // Check for patterns like "ID mayor que 5" at the very end
        if (filters.length === 0) {
            const endFilterPattern = /\b([a-záéíóúñ]+)\s+(mayor|menor|igual|contenga|contiene|sea|es)\s+(?:que|a)?\s*([^\s,]+)\s*$/i;
            const endMatch = command.match(endFilterPattern);

            if (endMatch) {
                const filter = this.parseFilterCondition(endMatch[0]);
                if (filter) {
                    filters.push(filter);
                }
            }
        }

        return filters;
    }

    parseFilterCondition(condition) {
        const cleanCondition = condition.trim();

        // Find field name
        let field = null;
        for (const [synonym, actualFields] of Object.entries(this.fieldSynonyms)) {
            if (cleanCondition.includes(synonym)) {
                field = actualFields[0]; // Use first matching field
                break;
            }
        }

        if (!field) return null;

        // Operator patterns
        const operators = {
            'mayor que': '>',
            'menor que': '<',
            'mayor o igual': '>=',
            'menor o igual': '<=',
            'igual a': '=',
            'sea': '=',
            'es': '=',
            'contenga': 'like',
            'contiene': 'like',
            'diferente de': '!=',
            'no sea': '!='
        };

        let operator = '=';
        let value = null;

        for (const [spanishOp, sqlOp] of Object.entries(operators)) {
            if (cleanCondition.includes(spanishOp)) {
                operator = sqlOp;
                // Extract value after operator
                const parts = cleanCondition.split(spanishOp);
                if (parts[1]) {
                    value = parts[1].trim();
                }
                break;
            }
        }

        // Convert some common value words
        if (value) {
            value = value.replace('activo', '1')
                .replace('inactivo', '0')
                .replace('verdadero', '1')
                .replace('falso', '0')
                .replace('sí', '1')
                .replace('si', '1')
                .replace('no', '0');
        }

        return value ? { field, operator, value } : null;
    }

    // Callback methods (to be overridden)
    onStart() {
        console.log('Voice recognition started');
    }

    onTranscript(text, isInterim) {
        console.log('Transcript:', text, 'Interim:', isInterim);
    }

    onEnd() {
        console.log('Voice recognition ended');
    }

    onError(error) {
        console.error('Voice recognition error:', error);
    }
}

// Export for use in other scripts
window.VoiceReportGenerator = VoiceReportGenerator;
