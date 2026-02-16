<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    @if (isset($users) && count($users) === 1)
        <title>{{ trans('general.assigned_to', ['name' => $users[0]->display_name]) }} - {{ date('Y-m-d H:i', time()) }}
        </title>
    @else
        <title>{{ trans('admin/users/general.print_assigned') }} - {{ date('Y-m-d H:i', time()) }}</title>
    @endisset

    <link rel="shortcut icon" type="image/ico"
        href="{{ $snipeSettings && $snipeSettings->favicon != '' ? Storage::disk('public')->url(e($snipeSettings->favicon)) : config('app.url') . '/favicon.ico' }}">

    <link rel="stylesheet" href="{{ url(mix('css/dist/bootstrap-table.css')) }}">

    {{-- stylesheets --}}
    <link rel="stylesheet" href="{{ url(mix('css/dist/all.css')) }}">

    <script nonce="{{ csrf_token() }}">
        window.snipeit = {
            settings: {
                "per_page": 50
            }
        };
    </script>

    <style>
        body {
            font-family: "Arial, Helvetica", sans-serif;
            padding: 20px;
        }

        table.inventory {
            width: 100%;
            border: 1px solid #d3d3d3;
        }

        @page {
            size: auto;
        }

        .print-logo {
            max-height: 40px;
        }

        h4 {
            margin-top: 20px;
            margin-bottom: 10px;
        }

        .table-usuarios-info td {
            text-align: center;
            vertical-align: middle;
        }

        .row-excluded {
            background-color: #f9f9f9 !important;
            opacity: 0.3;
            text-decoration: line-through;
        }

        @media print {
            .signature-boxes {
                page-break-after: always;
            }

            .row-cluded {
                display: none !important;
            }

        }

        media print {
            th {
                background-color: #0A4378 !important;
                color: #ffffff !important;

                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            table {
                border-collapse: collapse;
            }
        }
    </style>
</head>

<body>
@php
    $count = 0;
@endphp
{{-- If we are rendering multiple users we'll add the ability to show/hide EULAs for all of them at once via this button --}}
@if (count($users) > 1)

    <div class="pull-right hidden-print">
        <span>{{ trans('general.show_or_hide_eulas') }}</span>
        <button class="btn btn-default" type="button" data-toggle="collapse" data-target=".eula-row"
            aria-expanded="false" aria-controls="eula-row" title="EULAs">
            <i class="fa fa-eye-slash"></i>
        </button>
    </div>
@endif

@foreach ($users as $show_user)
    @php
        $count++;
    @endphp

    <div id="start_of_user_section"> {{-- used for page breaks when printing --}}</div>

    {{-- Encabezado Logo y Version de Acta --}}

    <table style="width: 100%; border-bottom: 1px solid #d3d3d3; margin-bottom: 20px;">
        <tr>
            <td style="width: 40%; border-bottom: 1px solid #d3d3d3; padding-bottom: 10px;">
                @if ($snipeSettings->acceptance_pdf_logo != '')
                    <img src="{{ config('app.url') }}/uploads/{{ $snipeSettings->acceptance_pdf_logo }}"
                        style="max-height: 40px; width: auto;">
                @else
                    <h3>{{ $snipeSettings->site_name }}</h3>
                @endif
            </td>
            <td style="width: 60%; vertical-align: middle; text-align: right; font-size: 16px;">
                <strong>ACTA DE DEVOLUCIÓN DE EQUIPOS DE TECNOLOGIA</strong><br>
                IT-INT-003<br>
                Versión 1.0
            </td>
        </tr>
    </table>

    <table class="table table-bordered table-condensed table-usuarios-info"
        style="margin-bottom: 20px; margin-top: 20px; width: 100%;">
        <tr>
            <th style="background-color: #0A4378; color: #fff; text-align: right; font-weight: bold">
                FECHA</th>
            <td>{{ Helper::getFormattedDateObject(now(), 'datetime', false) }}</td>
        </tr>
        <tr>
            <th style="background-color: #0A4378; color: #fff; text-align: right; font-weight: bold">ID DE USUARIO
            </th>
            <td>{{ $show_user->id != '' ? $show_user->id : 'N/A' }}</td>
        </tr>
        <tr>
            <th style="background-color: #0A4378; color: #fff; text-align: right; font-weight: bold">USUARIO</th>
            <td>{{ $show_user->display_name }}</td>
        </tr>
        <tr>
            <th style="background-color: #0A4378; color: #fff; text-align: right; font-weight: bold">EMPRESA</th>
            <td>{{ $show_user->company ? $show_user->company->name : 'N/A' }}</td>
        </tr>
        <tr>
            <th style="background-color: #0A4378; color: #fff; text-align: right; font-weight: bold">DEPARTAMENTO
            </th>
            <td>{{ $show_user->department ? $show_user->department->name : 'N/A' }}</td>
        </tr>
        <tr>
            <th style="background-color: #0A4378; color: #fff; text-align: right; font-weight: bold">CARGO</th>
            <td>{{ $show_user->jobtitle != '' ? $show_user->jobtitle : 'N/A' }}</td>
        </tr>
    </table>

    <div>
        @php
        $title = 'DETALLE DE DEVOLUCIONES';
        $type = 'return'; 
    @endphp

    <table style="margin-bottom: 20px; margin-top: 20px; width: 100%; border-collapse: collapse;" class="table table-bordered">
        <thead>
            <tr>
                <th colspan="3"
                    style="background-color: #0A4378; color: #fff; text-align: center; text-transform: uppercase; padding: 8px;">
                    {{ $title }}
                </th>
            </tr>
        </thead>
        <tbody>
            @foreach ($logs as $log)
                @php
                    // 1. OBTENEMOS EL ITEM RELACIONADO
                    $item = $log->item;

                    // 2. RECUPERAMOS LAS ESPECIFICACIONES AUTOMÁTICAS (Igual que el Partial)
                    // Si el ítem existe, traemos sus specs (RAM, CPU, etc). Si no, array vacío.
                    $specs = $item ? $item->getSpecsDisplayAttribute() : [];

                    // 3. DEFINIMOS LOS DATOS DEL LOG (Lo que querías agregar)
                    $logData = [
                        'Fecha Devolución' => $log->created_at->format('d/m/Y H:i'),
                        'Recibido Por'     => $log->user ? ($log->user->first_name . ' ' . $log->user->last_name) : 'Sistema',
                        // Si hay nota, la agregamos condicionalmente
                    ];
                    if($log->note) {
                        $logData['Notas'] = $log->note;
                    }

                    // 4. FUSIONAMOS: LOG + SPECS
                    // Esto pone primero la info de devolución y abajo las specs automáticas
                    $allSpecs = array_merge($logData, $specs);


                    // 5. OBTENER CATEGORÍA (Lógica del Partial)
                    $categoryName = $log->item_type;
                    if ($item) {
                        // Intentamos buscar la categoría real dependiendo del tipo de objeto
                        if (method_exists($item, 'model') && $item->model && $item->model->category) {
                            $categoryName = $item->model->category->name;
                        } elseif (method_exists($item, 'category') && $item->category) {
                            $categoryName = $item->category->name;
                        }
                    }
                    
                    $rowSpanValue = count($allSpecs);
                @endphp

                {{-- 6. RENDERIZADO (Idéntico al Partial, pero iterando nuestra lista fusionada) --}}
                @foreach ($allSpecs as $label => $value)
                    <tr class="{{ $type }}-spec-row">
                        
                        {{-- Columna Categoría (Solo la primera vez) --}}
                        @if ($loop->first)
                            <td rowspan="{{ $rowSpanValue }}" class="active"
                                style="text-align:center; vertical-align: middle; font-weight: bold; width: 20%; background-color: #f5f5f5;">
                                {{ $categoryName }}
                                <br>
                                {{-- Opcional: Mostrar el nombre del equipo debajo de la categoría --}}
                                <small style="font-weight: normal; color: #555;">
                                    {{ $item->name ?? $item->title ?? '' }}
                                </small>
                            </td>
                        @endif

                        {{-- Etiqueta --}}
                        <td style="font-weight: bold; width: 30%; padding: 5px;">
                            {{ $label }}
                        </td>

                        {{-- Valor --}}
                        <td style="width: 50%; padding: 5px;">
                            {!! $value !!} 
                        </td>
                    </tr>
                @endforeach

                {{-- Separador --}}
                @if (!$loop->parent->last)
                    <tr class="{{ $type }}-separator">
                        <td colspan="3" style="border-top: 2px solid #d3d3d3;"></td>
                    </tr>
                @endif

            @endforeach
        </tbody>
    </table>

    {{-- SECCIÓN DE FIRMAS --}}
    <div style="margin-top: 60px;">
        <table style="width: 100%; border: none;">
            <tr>
                <td style="width: 45%; border: none; text-align: center;">
                    __________________________________<br>
                    <strong>Entregado por:</strong><br>
                    {{ $users[0]->first_name }} {{ $users[0]->last_name }}
                </td>
                <td style="width: 10%; border: none;"></td>
                <td style="width: 45%; border: none; text-align: center;">
                    __________________________________<br>
                    <strong>Recibido por:</strong><br>
                    {{ auth()->user()->first_name }} {{ auth()->user()->last_name }}
                </td>
            </tr>
        </table>
    </div>
    </div>

    @include('partials.specs-table-with-toolbar', [
        'items' => $show_user->assets,
        'type' => 'asset',
        'title' => 'Equipos',
        'getCategoryCallback' => function ($item) {
            return $item->model && $item->model->category ? $item->model->category->name : 'Invalido';
        },
    ])

    @include('partials.specs-table-with-toolbar', [
        'items' => $show_user->licenses,
        'type' => 'license',
        'title' => 'Licencias',
        'getCategoryCallback' => function ($item) {
            return $item->category && $item->category ? $item->category->name : 'Invalido';
        },
    ])


    @include('partials.specs-table-with-toolbar', [
        'items' => $show_user->accessories,
        'type' => 'accessory',
        'title' => 'Accesorios',
        'getCategoryCallback' => function ($item) {
            return $item->category && $item->category ? $item->category->name : 'Invalido';
        },
    ])

    @include('partials.specs-table-with-toolbar', [
        'items' => $show_user->consumables,
        'type' => 'consumable',
        'title' => 'Consumibles',
        'getCategoryCallback' => function ($item) {
            return $item->category && $item->category ? $item->category->name : 'Invalido';
        },
    ])

    <div id="start_of_user_section"> {{-- used for page breaks when printing --}}</div>

    @php
        $eulas = [];
        foreach ($show_user->assets as $asset) {
            if ($asset->model && $asset->model->category && $asset->model->category->getEula()) {
                $eulas[] = $asset->model->category->getEula();
            }
        }
        foreach ($show_user->licenses as $license) {
            if ($license->category && $license->category->getEula()) {
                $eulas[] = $license->category->getEula();
            }
        }
        foreach ($show_user->accessories as $accessory) {
            if ($accessory->category && $accessory->category->getEula()) {
                $eulas[] = $accessory->category->getEula();
            }
        }
        foreach ($show_user->consumables as $consumable) {
            if ($consumable->category && $consumable->category->getEula()) {
                $eulas[] = $consumable->category->getEula();
            }
        }
    @endphp


    @php
        if (!empty($eulas)) {
            $eulas = array_unique($eulas);
        }
    @endphp
    {{-- This may have been render at the top of the page if we're rendering more than one user... --}}
    @if (count($users) === 1 && !empty($eulas))
        <p></p>
        <div class="pull-right">
            <button class="btn btn-default hidden-print" type="button" data-toggle="collapse"
                data-target=".eula-row" aria-expanded="false" aria-controls="eula-row" title="EULAs">
                <i class="fa fa-eye-slash"></i>
            </button>
        </div>
    @endif

    @foreach ($eulas as $eula)
        <div class="collapse eula-row" style="margin-top: 20px; margin-bottom: 20px;">
            <div style="border: 1px solid #d3d3d3; padding: 10px; max-height: 400px; overflow-y: auto;">
                {!! $eula !!}
            </div>
        </div>
    @endforeach


    {{-- Firmas - Estas 2 tablas deben estar en linea y centradas horizontal y verticalmente --}}
    <div
        style="display: flex; justify-content: space-around; align-items: center; margin-top: 40px; margin-bottom: 40px;">
        <table class="table table-bordered" style="margin-bottom: 20px; width: 25%">
            <thead>
                <tr style="background-color: #0A4378; color: #fff;">
                    <td style="text-align: center; font-weight: bold;">REVISADO POR:</td>
                </tr>
            </thead>
            <tbody>
                <tr style="height: 80px;">
                    <td>

                    </td>
                </tr>
                <tr>
                    <td style="text-align: center">{{ Auth::user()->name }}</td>
                </tr>
            </tbody>
        </table>

        <table class="table table-bordered" style="margin-bottom: 20px; width: 25%">
            <thead>
                <tr style="background-color: #0A4378; color: #fff;">
                    <td style="text-align: center; font-weight: bold;">RECIBE:</td>
                </tr>
            </thead>
            <tbody>
                <tr style="height: 80px;">
                    <td>

                    </td>
                </tr>
                <tr>
                    <td style="text-align: center">{{ $show_user->display_name }}</td>
                </tr>
            </tbody>
        </table>
    </div>
@endforeach

{{-- Javascript files --}}
<script src="{{ url(mix('js/dist/all.js')) }}" nonce="{{ csrf_token() }}"></script>

<script src="{{ url(mix('js/dist/bootstrap-table.js')) }}"></script>
<script src="{{ url(mix('js/dist/bootstrap-table-locale-all.min.js')) }}"></script>

<!-- load english again here, even though it's in the all.js file, because if BS table doesn't have the translation, it otherwise defaults to chinese. See https://bootstrap-table.com/docs/api/table-options/#locale -->
<script src="{{ url(mix('js/dist/bootstrap-table-en-US.min.js')) }}"></script>


<!-- Removedor de columnas para impresion -->
<script>
    function toggleRow(rowId) {
        var row = document.getElementById(rowId);
        if (row.classList.contains('row-excluded')) {
            row.classList.remove('row-excluded');
        } else {
            row.classList.add('row-excluded');
        }
    }

    // Sistema modular para mostrar/ocultar filas de especificaciones
    $(document).ready(function() {
        // Función genérica para ajustar rowspan de cualquier tipo de elemento
        function adjustCategoryRowspans(type) {
            var toolbarId = '#' + type + '-specs-toolbar';
            var $table = $(toolbarId).next('table');
            if ($table.length === 0) return;

            var tbody = $table.find('tbody');
            var groupStartRow = null;
            var visibleCount = 0;
            var rowClass = type + '-spec-row';
            var separatorClass = type + '-separator';

            tbody.find('tr').each(function() {
                var $row = $(this);

                // Si es una fila separadora, reiniciar el grupo
                if ($row.hasClass(separatorClass)) {
                    if (groupStartRow && visibleCount > 0) {
                        groupStartRow.find('td.active').attr('rowspan', visibleCount);
                    }
                    groupStartRow = null;
                    visibleCount = 0;
                    return;
                }

                // Si es una fila de especificación
                if ($row.hasClass(rowClass)) {
                    var $categoryCell = $row.find('td.active');

                    // Si tiene celda de categoría, es el inicio de un nuevo grupo
                    if ($categoryCell.length > 0) {
                        // Guardar el rowspan anterior si existe
                        if (groupStartRow && visibleCount > 0) {
                            groupStartRow.find('td.active').attr('rowspan', visibleCount);
                        }

                        // Iniciar nuevo grupo
                        groupStartRow = $row;
                        visibleCount = $row.is(':visible') ? 1 : 0;
                    } else {
                        // Es una fila continua del mismo grupo
                        if ($row.is(':visible')) {
                            visibleCount++;
                        }
                    }
                }
            });

            // Ajustar el último grupo
            if (groupStartRow && visibleCount > 0) {
                groupStartRow.find('td.active').attr('rowspan', visibleCount);
            }
        }

        // Función genérica para manejar el cambio de checkboxes
        function setupFieldToggle(type) {
            var toggleClass = '.' + type + '-field-toggle';
            var rowClass = '.' + type + '-spec-row';

            // Configurar el evento change para los checkboxes
            $(document).on('change', toggleClass, function() {
                var $checkbox = $(this);
                var isRequired = $checkbox.data('required') === 'true' || $checkbox.data('required') ===
                    true;

                // Si es un campo requerido (primer campo), no permitir ocultarlo
                if (isRequired && !$checkbox.is(':checked')) {
                    $checkbox.prop('checked', true);
                    return;
                }

                var fieldId = $checkbox.data('field');
                var isChecked = $checkbox.is(':checked');
                var rows = $(rowClass + '[data-field="' + fieldId + '"]');

                if (isChecked) {
                    rows.show();
                } else {
                    rows.hide();
                }

                // Ajustar rowspan de las celdas de categoría cuando se ocultan filas
                adjustCategoryRowspans(type);
            });
        }

        // Inicializar para cada tipo de elemento
        var types = ['asset', 'license', 'accessory', 'consumable'];
        types.forEach(function(type) {
            setupFieldToggle(type);
        });
    });
</script>

<script>
    $('.snipe-table').bootstrapTable('destroy').each(function() {
        console.log('BS table loaded');

        data_export_options = $(this).attr('data-export-options');
        export_options = data_export_options ? JSON.parse(data_export_options) : {};
        export_options['htmlContent'] = false; // this is already the default; but let's be explicit about it
        export_options['jspdf'] = {
            "orientation": "l"
        };
        // the following callback method is necessary to prevent XSS vulnerabilities
        // (this is taken from Bootstrap Tables's default wrapper around jQuery Table Export)
        export_options['onCellHtmlData'] = function(cell, rowIndex, colIndex, htmlData) {
            if (cell.is('th')) {
                return cell.find('.th-inner').text()
            }
            return htmlData
        }
        $(this).bootstrapTable({
            classes: 'table table-responsive table-no-bordered',
            ajaxOptions: {
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            },
            // reorderableColumns: true,
            stickyHeader: true,
            stickyHeaderOffsetLeft: parseInt($('body').css('padding-left'), 10),
            stickyHeaderOffsetRight: parseInt($('body').css('padding-right'), 10),
            undefinedText: '',
            iconsPrefix: 'fa',
            cookieStorage: '{{ config('session.bs_table_storage') }}',
            cookie: true,
            cookieExpire: '2y',
            mobileResponsive: true,
            maintainSelected: true,
            trimOnSearch: false,
            showSearchClearButton: true,
            paginationFirstText: "{{ trans('general.first') }}",
            paginationLastText: "{{ trans('general.last') }}",
            paginationPreText: "{{ trans('general.previous') }}",
            paginationNextText: "{{ trans('general.next') }}",
            pageList: ['10', '20', '30', '50', '100', '150', '200'
                {!! config('app.max_results') > 200 ? ",'500'" : '' !!}{!! config('app.max_results') > 500 ? ",'" . config('app.max_results') . "'" : '' !!}
            ],
            pageSize: {{ $snipeSettings->per_page != '' && $snipeSettings->per_page > 0 ? $snipeSettings->per_page : 20 }},
            paginationVAlign: 'both',
            queryParams: function(params) {
                var newParams = {};
                for (var i in params) {
                    if (!keyBlocked(i)) { // only send the field if it's not in blockedFields
                        newParams[i] = params[i];
                    }
                }
                return newParams;
            },
            formatLoadingMessage: function() {
                return '<h2><i class="fas fa-spinner fa-spin" aria-hidden="true"></i> {{ trans('general.loading') }} </h2>';
            },
            icons: {
                advancedSearchIcon: 'fas fa-search-plus',
                paginationSwitchDown: 'fa-caret-square-o-down',
                paginationSwitchUp: 'fa-caret-square-o-up',
                fullscreen: 'fa-expand',
                columns: 'fa-columns',
                refresh: 'fas fa-sync-alt',
                export: 'fa-download',
                clearSearch: 'fa-times'
            },
            exportOptions: export_options,

            exportTypes: ['xlsx', 'excel', 'csv', 'pdf', 'json', 'xml', 'txt', 'sql', 'doc'],
            onLoadSuccess: function() {
                $('[data-tooltip="true"]').tooltip(); // Needed to attach tooltips after ajax call
            }

        });
    });
</script>

</body>

</html>
