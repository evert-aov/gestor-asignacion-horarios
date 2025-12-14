<div>
    <x-table-header>
        <div class="flex items-center space-x-2">
            <x-icons.key class="w-4 h-4" /> <span>{{ __('Sigla') }}</span>
        </div>
    </x-table-header>

    <x-table-header>
        <div class="flex items-center space-x-2">
            <x-icons.number class="w-4 h-4" /> <span>{{ __('GR') }}</span>
        </div>
    </x-table-header>

    <x-table-header>
        <div class="flex items-center space-x-2">
            <x-icons.classroom class="w-4 h-4" /> <span>{{ __('Subject') }}</span>
        </div>
    </x-table-header>

    <x-table-header>
        <div class="flex items-center space-x-2">
            <x-icons.users class="w-4 h-4" /> <span>{{ __('Teacher') }}</span>
        </div>
    </x-table-header>

    @php
        $days = [__('Monday'), __('Tuesday'), __('Wednesday'), __('Thursday'), __('Friday'), __('Saturday')];
    @endphp
    {{-- 3 columnas para horarios con aulas --}}
    @for($i = 0; $i < 6; $i++)
        <x-table-header>
            <div class="flex items-center space-x-2">
                <x-icons.timedate class="w-4 h-4" /> <x-input-label for="day_{{ $i }}" :value="__($days[$i])"/>
            </div>
        </x-table-header>

        <x-table-header>
            <div class="flex items-center space-x-2">
                <x-icons.time class="w-4 h-4" /> <span>{{ __('Hour/Room') }}</span>
            </div>
        </x-table-header>
    @endfor

    <x-table-header>
        <div class="flex items-center space-x-2">
            <x-icons.settings class="w-4 h-4" /> <span>{{ __('Action') }}</span>
        </div>
    </x-table-header>
</div>
