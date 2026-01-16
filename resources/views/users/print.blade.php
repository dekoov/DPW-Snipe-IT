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

{{-- @if ($snipeSettings->logo_print_assets == '1')
@if ($snipeSettings->brand == '3')

<h2>
@if ($snipeSettings->acceptance_pdf_logo != '')
<img class="print-logo" src="{{ config('app.url') }}/uploads/{{ $snipeSettings->acceptance_pdf_logo }}">
@endif
{{ $snipeSettings->site_name }}
</h2>
@elseif ($snipeSettings->brand == '2')
@if ($snipeSettings->acceptance_pdf_logo != '')
<img class="print-logo" src="{{ config('app.url') }}/uploads/{{ $snipeSettings->acceptance_pdf_logo }}">
@endif
@else
<h2>{{ $snipeSettings->site_name }}</h2>
@endif
@endif --}}

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
                <strong>ACTA DE ENTREGA DE EQUIPOS DE TECNOLOGIA</strong><br>
                IT-INT-003<br>
                Versión 1.0
            </td>
        </tr>
    </table>

    <table class=" table table-bordered table-condensed"
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


    @include('partials.specs-table-with-toolbar', [
        'items' => $show_user->assets,
        'type' => 'asset',
        'title' => 'Equipos',
        'getCategoryCallback' => function($item) {
            return $item->model && $item->model->category ? $item->model->category->name : 'Invalido';
        }
    ])

    @include('partials.specs-table-with-toolbar', [
        'items' => $show_user->licenses,
        'type' => 'license',
        'title' => 'Licencias',
        'getCategoryCallback' => function($item) {
            return $item->category && $item->category ? $item->category->name : 'Invalido';
        }
    ])


    @include('partials.specs-table-with-toolbar', [
        'items' => $show_user->accessories,
        'type' => 'accessory',
        'title' => 'Accesorios',
        'getCategoryCallback' => function($item) {
            return $item->category && $item->category ? $item->category->name : 'Invalido';
        }
    ])

    @include('partials.specs-table-with-toolbar', [
        'items' => $show_user->consumables,
        'type' => 'consumable',
        'title' => 'Consumibles',
        'getCategoryCallback' => function($item) {
            return $item->category && $item->category ? $item->category->name : 'Invalido';
        }
    ])

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

<table style="margin-top: 80px;" class="{{ count($users) > $count ? 'signature-boxes' : '' }}">
    @if (!empty($eulas))
        <tr class="collapse eula-row">
            <td style="padding-right: 10px; vertical-align: top; font-weight: bold;">EULA</td>
            <td style="padding-right: 10px; vertical-align: top; padding-bottom: 80px;" colspan="3">
                @foreach ($eulas as $key => $eula)
                    {!! $eula !!}
                @endforeach
            </td>
        </tr>
    @endif
    <tr>
        <td style="padding-right: 10px; vertical-align: top; font-weight: bold;">
            {{ trans('general.signed_off_by') }}:</td>
        <td style="padding-right: 10px; vertical-align: top;">______________________________________</td>
        <td style="padding-right: 10px; vertical-align: top;">______________________________________</td>
        <td>_____________</td>
    </tr>
    <tr style="height: 80px;">
        <td></td>
        <td style="padding-right: 10px; vertical-align: top;">{{ trans('general.name') }}</td>
        <td style="padding-right: 10px; vertical-align: top;">{{ trans('general.signature') }}</td>
        <td style="padding-right: 10px; vertical-align: top;">{{ trans('general.date') }}</td>
    </tr>
    <tr>
        <td style="padding-right: 10px; vertical-align: top; font-weight: bold;">
            {{ trans('admin/users/table.manager') }}:</td>
        <td style="padding-right: 10px; vertical-align: top;">______________________________________</td>
        <td style="padding-right: 10px; vertical-align: top;">______________________________________</td>
        <td>_____________</td>
    </tr>
    <tr>
        <td></td>
        <td style="padding-right: 10px; vertical-align: top;">{{ trans('general.name') }}</td>
        <td style="padding-right: 10px; vertical-align: top;">{{ trans('general.signature') }}</td>
        <td style="padding-right: 10px; vertical-align: top;">{{ trans('general.date') }}</td>
        <td></td>
    </tr>

</table>
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
                var isRequired = $checkbox.data('required') === 'true' || $checkbox.data('required') === true;
                
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
