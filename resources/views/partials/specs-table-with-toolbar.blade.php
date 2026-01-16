{{-- 
    Partial reutilizable para mostrar tablas de especificaciones con toolbar de checkboxes
    
    Uso:
    @include('partials.specs-table-with-toolbar', [
        'items' => $show_user->assets,
        'type' => 'asset',
        'title' => 'Equipos',
        'getCategoryCallback' => function($item) {
            return $item->model && $item->model->category ? $item->model->category->name : 'Invalido';
        }
    ])
    
    Parámetros requeridos:
    - $items: Colección de items (assets, licenses, accessories, consumables)
    - $type: Tipo de elemento ('asset', 'license', 'accessory', 'consumable')
    - $title: Título de la tabla
    - $getCategoryCallback: Función callback para obtener el nombre de la categoría del item
--}}

@if (isset($items) && $items && $items->count() > 0)
    @php
        // Recopilar todos los campos únicos de todos los items
        $allFields = [];
        foreach ($items as $item) {
            $specs = $item->getSpecsDisplayAttribute();
            foreach ($specs as $label => $value) {
                if (!in_array($label, $allFields)) {
                    $allFields[] = $label;
                }
            }
        }
       $allFields;
    @endphp

    <div id="{{ $type }}-specs-toolbar">
        @if (count($allFields) > 0)
            <div class="hidden-print" style="display: inline-block; margin-top: 10px;">
                <strong style="margin-right: 10px;">Mostrar campos:</strong>
                @foreach ($allFields as $index => $field)
                    <label style="margin-right: 15px; font-weight: normal;">
                        <input type="checkbox" 
                               class="{{ $type }}-field-toggle" 
                               data-field="{{ md5($field) }}" 
                               data-required="{{ $index === 0 ? 'true' : 'false' }}"
                               @if($index === 0) disabled @endif
                               checked 
                               style="margin-right: 5px;">
                        {{ $field }}
                    </label>
                @endforeach
            </div>
        @endif
    </div>

    <table style="margin-bottom: 20px; margin-top: 20px; width: 100%;" class="table table-bordered">
        <thead>
            <tr>
                <th colspan="3"
                    style="background-color: #0A4378; color: #fff; text-align: center; text-transform: uppercase;">
                    {{ $title }}
                </th>
            </tr>
        </thead>
        <tbody>
            @foreach ($items as $item)
                @php
                    $specs = $item->getSpecsDisplayAttribute();
                    $rowSpanValue = count($specs);
                    $categoryName = $getCategoryCallback($item);
                @endphp

                @foreach ($specs as $label => $value)
                    <tr class="{{ $type }}-spec-row" data-field="{{ md5($label) }}">
                        @if ($loop->first)
                            <td rowspan="{{ $rowSpanValue }}" class="active"
                                style="text-align:center; vertical-align: middle; font-weight: bold;">
                                {{ $categoryName }}
                            </td>
                        @endif

                        <td style="font-weight: bold; width: 30%;">
                            {{ $label }}
                        </td>
                        <td style="width: 50%;">
                            {{ $value }}
                        </td>
                    </tr>
                @endforeach

                @if (!$loop->parent->last)
                    <tr class="{{ $type }}-separator">
                        <td colspan="3" style="border-top: 2px solid #d3d3d3;"></td>
                    </tr>
                @endif
            @endforeach
        </tbody>
    </table>
@endif
