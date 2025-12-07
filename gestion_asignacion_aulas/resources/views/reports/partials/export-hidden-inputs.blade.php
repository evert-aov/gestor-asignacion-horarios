@csrf
<input type="hidden" name="table" value="{{ $table }}">
@foreach ($selectedFields as $field)
    <input type="hidden" name="fields[]" value="{{ $field }}">
@endforeach
@foreach ($filters as $index => $filter)
    <input type="hidden" name="filters[{{ $index }}][field]" value="{{ $filter['field'] }}">
    <input type="hidden" name="filters[{{ $index }}][operator]" value="{{ $filter['operator'] }}">
    @if (isset($filter['value']))
        <input type="hidden" name="filters[{{ $index }}][value]" value="{{ $filter['value'] }}">
    @endif
    @if (isset($filter['value2']))
        <input type="hidden" name="filters[{{ $index }}][value2]" value="{{ $filter['value2'] }}">
    @endif
@endforeach
