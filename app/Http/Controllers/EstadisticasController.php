<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use ZipArchive;

class EstadisticasController extends Controller
{
    public function index(Request $request)
    {
        $tipoVista = $request->get('tipo_vista', 'mes');
        $area = $request->get('area', 'general');

        $fecha = $request->get('fecha', now()->toDateString());
        $mes = $request->get('mes', now()->format('Y-m'));
        $anio = $request->get('anio', now()->format('Y'));
        $desde = $request->get('desde');
        $hasta = $request->get('hasta');

        [$fechaInicio, $fechaFin, $agrupacion, $tituloPeriodo] = $this->resolverPeriodo(
            $tipoVista,
            $fecha,
            $mes,
            $anio,
            $desde,
            $hasta
        );

        $alquileresRegistrados = DB::table('alquileres')
            ->whereBetween('fecha_alquiler', [$fechaInicio->toDateString(), $fechaFin->toDateString()])
            ->where('estado', '!=', 'CANCELADO')
            ->count();

        $pagosRegistrados = DB::table('pagos')
            ->whereBetween(DB::raw('DATE(created_at)'), [$fechaInicio->toDateString(), $fechaFin->toDateString()])
            ->count();

        $ingresosRecibidos = DB::table('pagos')
            ->whereBetween(DB::raw('DATE(created_at)'), [$fechaInicio->toDateString(), $fechaFin->toDateString()])
            ->sum('monto');

        $descuentosAplicados = DB::table('pagos')
            ->whereBetween(DB::raw('DATE(created_at)'), [$fechaInicio->toDateString(), $fechaFin->toDateString()])
            ->sum('descuento_aplicado');

        $moraGenerada = DB::table('alquileres')
            ->whereBetween(DB::raw('DATE(fecha_hora_devolucion_real)'), [$fechaInicio->toDateString(), $fechaFin->toDateString()])
            ->where('estado', '!=', 'CANCELADO')
            ->sum('monto_mora');

        $saldoPendienteActual = DB::table('alquileres')
            ->where('estado', '!=', 'CANCELADO')
            ->sum('saldo_pendiente');

        $productoMasAlquilado = DB::table('alquiler_detalles')
            ->join('productos', 'productos.id', '=', 'alquiler_detalles.producto_id')
            ->join('alquileres', 'alquileres.id', '=', 'alquiler_detalles.alquiler_id')
            ->whereBetween('alquileres.fecha_alquiler', [$fechaInicio->toDateString(), $fechaFin->toDateString()])
            ->where('alquileres.estado', '!=', 'CANCELADO')
            ->select(
                'productos.nombre',
                DB::raw('SUM(alquiler_detalles.cantidad) as total_alquilado')
            )
            ->groupBy('productos.id', 'productos.nombre')
            ->orderByDesc('total_alquilado')
            ->first();

        $tallaTogaMasAlquilada = DB::table('alquiler_detalles')
            ->join('productos', 'productos.id', '=', 'alquiler_detalles.producto_id')
            ->join('producto_togas', 'producto_togas.producto_id', '=', 'productos.id')
            ->join('alquileres', 'alquileres.id', '=', 'alquiler_detalles.alquiler_id')
            ->where('productos.tipo_producto', 'TOGA')
            ->whereBetween('alquileres.fecha_alquiler', [$fechaInicio->toDateString(), $fechaFin->toDateString()])
            ->where('alquileres.estado', '!=', 'CANCELADO')
            ->select(
                'producto_togas.talla',
                DB::raw('SUM(alquiler_detalles.cantidad) as total_alquilado')
            )
            ->groupBy('producto_togas.talla')
            ->orderByDesc('total_alquilado')
            ->first();

        $alquileresPorPeriodo = $this->alquileresPorPeriodo($fechaInicio, $fechaFin, $agrupacion);
        $pagosPorPeriodo = $this->pagosPorPeriodo($fechaInicio, $fechaFin, $agrupacion);
        $moraPorPeriodo = $this->moraPorPeriodo($fechaInicio, $fechaFin, $agrupacion);

        $periodos = collect()
            ->merge($alquileresPorPeriodo->pluck('periodo'))
            ->merge($pagosPorPeriodo->pluck('periodo'))
            ->merge($moraPorPeriodo->pluck('periodo'))
            ->unique()
            ->sort()
            ->values();

        $tablaResumen = $periodos->map(function ($periodo) use ($alquileresPorPeriodo, $pagosPorPeriodo, $moraPorPeriodo) {
            $alquiler = $alquileresPorPeriodo->firstWhere('periodo', $periodo);
            $pago = $pagosPorPeriodo->firstWhere('periodo', $periodo);
            $mora = $moraPorPeriodo->firstWhere('periodo', $periodo);

            $ingresos = $pago->ingresos ?? 0;
            $descuentos = $pago->descuentos ?? 0;
            $moraGenerada = $mora->mora ?? 0;

            return [
                'periodo' => $periodo,
                'alquileres' => $alquiler->total ?? 0,
                'pagos' => $pago->pagos ?? 0,
                'ingresos' => $ingresos,
                'descuentos' => $descuentos,
                'mora' => $moraGenerada,
                'total_aplicado' => $ingresos + $descuentos,
            ];
        });

        $chartLabels = $tablaResumen->pluck('periodo')->values();
        $chartAlquileres = $tablaResumen->pluck('alquileres')->values();
        $chartIngresos = $tablaResumen->pluck('ingresos')->values();
        $chartDescuentos = $tablaResumen->pluck('descuentos')->values();
        $chartMora = $tablaResumen->pluck('mora')->values();

        $diaMasAlquileres = $this->diaMasAlquileres($fechaInicio, $fechaFin);
        $diaMasIngresos = $this->diaMasIngresos($fechaInicio, $fechaFin);
        $diaMasDescuentos = $this->diaMasDescuentos($fechaInicio, $fechaFin);
        $diaMasMora = $this->diaMasMora($fechaInicio, $fechaFin);
        $metodoPagoMasUsado = $this->metodoPagoMasUsado($fechaInicio, $fechaFin);
        $institucionMasAlquileres = $this->institucionMasAlquileres($fechaInicio, $fechaFin);
        $topProductos = $this->topProductos($fechaInicio, $fechaFin);
        $topTallasToga = $this->topTallasToga($fechaInicio, $fechaFin);

        return view('estadisticas.index', compact(
            'tipoVista',
            'area',
            'fecha',
            'mes',
            'anio',
            'desde',
            'hasta',
            'fechaInicio',
            'fechaFin',
            'tituloPeriodo',
            'alquileresRegistrados',
            'pagosRegistrados',
            'ingresosRecibidos',
            'descuentosAplicados',
            'moraGenerada',
            'saldoPendienteActual',
            'productoMasAlquilado',
            'tallaTogaMasAlquilada',
            'tablaResumen',
            'chartLabels',
            'chartAlquileres',
            'chartIngresos',
            'chartDescuentos',
            'chartMora',

            'diaMasAlquileres',
            'diaMasIngresos',
            'diaMasDescuentos',
            'diaMasMora',
            'metodoPagoMasUsado',
            'institucionMasAlquileres',
            'topProductos',
            'topTallasToga',
        ));
    }

    private function resolverPeriodo($tipoVista, $fecha, $mes, $anio, $desde, $hasta): array
    {
        if ($tipoVista === 'dia') {
            $inicio = Carbon::parse($fecha)->startOfDay();
            $fin = Carbon::parse($fecha)->endOfDay();

            return [$inicio, $fin, 'dia', 'Día ' . $inicio->format('d/m/Y')];
        }

        if ($tipoVista === 'anio') {
            $inicio = Carbon::createFromDate($anio, 1, 1)->startOfYear();
            $fin = Carbon::createFromDate($anio, 1, 1)->endOfYear();

            return [$inicio, $fin, 'mes', 'Año ' . $anio];
        }

        if ($tipoVista === 'rango') {
            $inicio = $desde ? Carbon::parse($desde)->startOfDay() : now()->startOfMonth();
            $fin = $hasta ? Carbon::parse($hasta)->endOfDay() : now()->endOfMonth();

            if ($fin->lt($inicio)) {
                $fin = $inicio->copy()->endOfDay();
            }

            return [$inicio, $fin, 'dia', 'Del ' . $inicio->format('d/m/Y') . ' al ' . $fin->format('d/m/Y')];
        }

        $inicio = Carbon::parse($mes . '-01')->startOfMonth();
        $fin = Carbon::parse($mes . '-01')->endOfMonth();

        return [$inicio, $fin, 'dia', 'Mes ' . $inicio->translatedFormat('F Y')];
    }

    private function alquileresPorPeriodo($inicio, $fin, $agrupacion)
    {
        $selectPeriodo = $agrupacion === 'mes'
            ? "DATE_FORMAT(fecha_alquiler, '%Y-%m')"
            : "DATE(fecha_alquiler)";

        return DB::table('alquileres')
            ->whereBetween('fecha_alquiler', [$inicio->toDateString(), $fin->toDateString()])
            ->where('estado', '!=', 'CANCELADO')
            ->selectRaw("$selectPeriodo as periodo, COUNT(*) as total")
            ->groupByRaw($selectPeriodo)
            ->orderBy('periodo')
            ->get();
    }

    private function pagosPorPeriodo($inicio, $fin, $agrupacion)
    {
        $selectPeriodo = $agrupacion === 'mes'
            ? "DATE_FORMAT(created_at, '%Y-%m')"
            : "DATE(created_at)";

        return DB::table('pagos')
            ->whereBetween(DB::raw('DATE(created_at)'), [$inicio->toDateString(), $fin->toDateString()])
            ->selectRaw("
                $selectPeriodo as periodo,
                COUNT(*) as pagos,
                SUM(monto) as ingresos,
                SUM(descuento_aplicado) as descuentos
            ")
            ->groupByRaw($selectPeriodo)
            ->orderBy('periodo')
            ->get();
    }

    private function moraPorPeriodo($inicio, $fin, $agrupacion)
    {
        $selectPeriodo = $agrupacion === 'mes'
            ? "DATE_FORMAT(fecha_hora_devolucion_real, '%Y-%m')"
            : "DATE(fecha_hora_devolucion_real)";

        return DB::table('alquileres')
            ->whereNotNull('fecha_hora_devolucion_real')
            ->whereBetween(DB::raw('DATE(fecha_hora_devolucion_real)'), [$inicio->toDateString(), $fin->toDateString()])
            ->where('estado', '!=', 'CANCELADO')
            ->selectRaw("$selectPeriodo as periodo, SUM(monto_mora) as mora")
            ->groupByRaw($selectPeriodo)
            ->orderBy('periodo')
            ->get();
    }

    private function diaMasAlquileres($inicio, $fin)
    {
        return DB::table('alquileres')
            ->whereBetween('fecha_alquiler', [$inicio->toDateString(), $fin->toDateString()])
            ->where('estado', '!=', 'CANCELADO')
            ->selectRaw('DATE(fecha_alquiler) as periodo, COUNT(*) as total')
            ->groupByRaw('DATE(fecha_alquiler)')
            ->orderByDesc('total')
            ->orderBy('periodo')
            ->first();
    }

    private function diaMasIngresos($inicio, $fin)
    {
        return DB::table('pagos')
            ->whereBetween(DB::raw('DATE(created_at)'), [$inicio->toDateString(), $fin->toDateString()])
            ->selectRaw('DATE(created_at) as periodo, SUM(monto) as total')
            ->groupByRaw('DATE(created_at)')
            ->orderByDesc('total')
            ->orderBy('periodo')
            ->first();
    }

    private function diaMasDescuentos($inicio, $fin)
    {
        return DB::table('pagos')
            ->whereBetween(DB::raw('DATE(created_at)'), [$inicio->toDateString(), $fin->toDateString()])
            ->selectRaw('DATE(created_at) as periodo, SUM(descuento_aplicado) as total')
            ->groupByRaw('DATE(created_at)')
            ->havingRaw('SUM(descuento_aplicado) > 0')
            ->orderByDesc('total')
            ->orderBy('periodo')
            ->first();
    }

    private function diaMasMora($inicio, $fin)
    {
        return DB::table('alquileres')
            ->whereNotNull('fecha_hora_devolucion_real')
            ->whereBetween(DB::raw('DATE(fecha_hora_devolucion_real)'), [$inicio->toDateString(), $fin->toDateString()])
            ->where('estado', '!=', 'CANCELADO')
            ->selectRaw('DATE(fecha_hora_devolucion_real) as periodo, SUM(monto_mora) as total')
            ->groupByRaw('DATE(fecha_hora_devolucion_real)')
            ->havingRaw('SUM(monto_mora) > 0')
            ->orderByDesc('total')
            ->orderBy('periodo')
            ->first();
    }

    private function metodoPagoMasUsado($inicio, $fin)
    {
        return DB::table('pagos')
            ->whereBetween(DB::raw('DATE(created_at)'), [$inicio->toDateString(), $fin->toDateString()])
            ->selectRaw('metodo_pago, COUNT(*) as total, SUM(monto) as ingresos')
            ->groupBy('metodo_pago')
            ->orderByDesc('total')
            ->orderByDesc('ingresos')
            ->first();
    }

    private function institucionMasAlquileres($inicio, $fin)
    {
        return DB::table('alquileres')
            ->whereBetween('fecha_alquiler', [$inicio->toDateString(), $fin->toDateString()])
            ->where('estado', '!=', 'CANCELADO')
            ->whereNotNull('institucion_representada')
            ->where('institucion_representada', '!=', '')
            ->selectRaw('institucion_representada, COUNT(*) as total')
            ->groupBy('institucion_representada')
            ->orderByDesc('total')
            ->orderBy('institucion_representada')
            ->first();
    }

    private function topProductos($inicio, $fin)
    {
        return DB::table('alquiler_detalles')
            ->join('productos', 'productos.id', '=', 'alquiler_detalles.producto_id')
            ->join('alquileres', 'alquileres.id', '=', 'alquiler_detalles.alquiler_id')
            ->whereBetween('alquileres.fecha_alquiler', [$inicio->toDateString(), $fin->toDateString()])
            ->where('alquileres.estado', '!=', 'CANCELADO')
            ->selectRaw('
                productos.nombre,
                productos.tipo_producto,
                SUM(alquiler_detalles.cantidad) as total
            ')
            ->groupBy('productos.id', 'productos.nombre', 'productos.tipo_producto')
            ->orderByDesc('total')
            ->limit(5)
            ->get();
    }

    private function topTallasToga($inicio, $fin)
    {
        return DB::table('alquiler_detalles')
            ->join('productos', 'productos.id', '=', 'alquiler_detalles.producto_id')
            ->join('producto_togas', 'producto_togas.producto_id', '=', 'productos.id')
            ->join('alquileres', 'alquileres.id', '=', 'alquiler_detalles.alquiler_id')
            ->where('productos.tipo_producto', 'TOGA')
            ->whereBetween('alquileres.fecha_alquiler', [$inicio->toDateString(), $fin->toDateString()])
            ->where('alquileres.estado', '!=', 'CANCELADO')
            ->selectRaw('
                producto_togas.talla,
                SUM(alquiler_detalles.cantidad) as total
            ')
            ->groupBy('producto_togas.talla')
            ->orderByDesc('total')
            ->limit(5)
            ->get();
    }

    public function exportarPdf(Request $request)
    {
        $datos = $this->obtenerDatosExportacion($request);

        $datos['graficoAlquileresImagen'] = $request->input('grafico_alquileres');
        $datos['graficoFinancieroImagen'] = $request->input('grafico_financiero');

        $nombreArchivo = 'estadisticas_' . now()->format('Ymd_His') . '.pdf';

        $pdf = Pdf::loadView('estadisticas.pdf', $datos)
            ->setPaper('letter', 'portrait');

        return $pdf->download($nombreArchivo);
    }

    public function exportarXlsx(Request $request)
    {
        $datos = $this->obtenerDatosExportacion($request);

        $nombreArchivo = 'estadisticas_' . now()->format('Ymd_His') . '.xlsx';
        $rutaTemporal = storage_path('app/' . $nombreArchivo);

        $this->crearXlsxEstadisticas($rutaTemporal, $datos);

        return response()->download($rutaTemporal, $nombreArchivo)->deleteFileAfterSend(true);
    }

    private function crearXlsxEstadisticas(string $rutaArchivo, array $datos): void
    {
        $zip = new ZipArchive();

        if ($zip->open($rutaArchivo, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException('No se pudo crear el archivo XLSX.');
        }

        $zip->addFromString('[Content_Types].xml', $this->xlsxContentTypesEstadisticas());
        $zip->addFromString('_rels/.rels', $this->xlsxRels());
        $zip->addFromString('xl/workbook.xml', $this->xlsxWorkbookEstadisticas());
        $zip->addFromString('xl/_rels/workbook.xml.rels', $this->xlsxWorkbookRelsEstadisticas());
        $zip->addFromString('xl/styles.xml', $this->xlsxStylesEstadisticas());

        $zip->addFromString('xl/worksheets/sheet1.xml', $this->xlsxHojaResumen($datos));
        $zip->addFromString('xl/worksheets/sheet2.xml', $this->xlsxHojaTablaResumen($datos));
        $zip->addFromString('xl/worksheets/sheet3.xml', $this->xlsxHojaRankings($datos));
        $zip->addFromString('xl/worksheets/sheet4.xml', $this->xlsxHojaProductos($datos));
        $zip->addFromString('xl/worksheets/sheet5.xml', $this->xlsxHojaTallas($datos));

        $zip->close();
    }

    private function xlsxRels(): string
    {
        return '<?xml version="1.0" encoding="UTF-8"?>
    <Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
        <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>
    </Relationships>';
    }

    private function xlsxContentTypesEstadisticas(): string
    {
        return '<?xml version="1.0" encoding="UTF-8"?>
    <Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
        <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
        <Default Extension="xml" ContentType="application/xml"/>
        <Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>
        <Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>
        <Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>
        <Override PartName="/xl/worksheets/sheet2.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>
        <Override PartName="/xl/worksheets/sheet3.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>
        <Override PartName="/xl/worksheets/sheet4.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>
        <Override PartName="/xl/worksheets/sheet5.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>
    </Types>';
    }

    private function xlsxWorkbookEstadisticas(): string
    {
        return '<?xml version="1.0" encoding="UTF-8"?>
    <workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"
            xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
        <sheets>
            <sheet name="Resumen" sheetId="1" r:id="rId1"/>
            <sheet name="Tabla resumen" sheetId="2" r:id="rId2"/>
            <sheet name="Rankings" sheetId="3" r:id="rId3"/>
            <sheet name="Productos" sheetId="4" r:id="rId4"/>
            <sheet name="Tallas" sheetId="5" r:id="rId5"/>
        </sheets>
    </workbook>';
    }

    private function xlsxWorkbookRelsEstadisticas(): string
    {
        return '<?xml version="1.0" encoding="UTF-8"?>
    <Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
        <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>
        <Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet2.xml"/>
        <Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet3.xml"/>
        <Relationship Id="rId4" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet4.xml"/>
        <Relationship Id="rId5" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet5.xml"/>
        <Relationship Id="rId6" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>
    </Relationships>';
    }

    private function xlsxStylesEstadisticas(): string
    {
        return '<?xml version="1.0" encoding="UTF-8"?>
    <styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
        <numFmts count="1">
            <numFmt numFmtId="164" formatCode="&quot;Q&quot;#,##0.00"/>
        </numFmts>

        <fonts count="6">
            <font>
                <sz val="11"/>
                <color rgb="FF111827"/>
                <name val="Calibri"/>
            </font>

            <font>
                <b/>
                <sz val="18"/>
                <color rgb="FFFFFFFF"/>
                <name val="Calibri"/>
            </font>

            <font>
                <b/>
                <sz val="11"/>
                <color rgb="FFFFFFFF"/>
                <name val="Calibri"/>
            </font>

            <font>
                <b/>
                <sz val="11"/>
                <color rgb="FF111827"/>
                <name val="Calibri"/>
            </font>

            <font>
                <sz val="10"/>
                <color rgb="FF64748B"/>
                <name val="Calibri"/>
            </font>

            <font>
                <b/>
                <sz val="11"/>
                <color rgb="FF1D4ED8"/>
                <name val="Calibri"/>
            </font>
        </fonts>

        <fills count="7">
            <fill>
                <patternFill patternType="none"/>
            </fill>

            <fill>
                <patternFill patternType="gray125"/>
            </fill>

            <fill>
                <patternFill patternType="solid">
                    <fgColor rgb="FF1D4ED8"/>
                    <bgColor indexed="64"/>
                </patternFill>
            </fill>

            <fill>
                <patternFill patternType="solid">
                    <fgColor rgb="FF0F172A"/>
                    <bgColor indexed="64"/>
                </patternFill>
            </fill>

            <fill>
                <patternFill patternType="solid">
                    <fgColor rgb="FFEFF6FF"/>
                    <bgColor indexed="64"/>
                </patternFill>
            </fill>

            <fill>
                <patternFill patternType="solid">
                    <fgColor rgb="FFF8FAFC"/>
                    <bgColor indexed="64"/>
                </patternFill>
            </fill>

            <fill>
                <patternFill patternType="solid">
                    <fgColor rgb="FFFFFFFF"/>
                    <bgColor indexed="64"/>
                </patternFill>
            </fill>
        </fills>

        <borders count="2">
            <border>
                <left/>
                <right/>
                <top/>
                <bottom/>
                <diagonal/>
            </border>

            <border>
                <left style="thin"><color rgb="FFE2E8F0"/></left>
                <right style="thin"><color rgb="FFE2E8F0"/></right>
                <top style="thin"><color rgb="FFE2E8F0"/></top>
                <bottom style="thin"><color rgb="FFE2E8F0"/></bottom>
                <diagonal/>
            </border>
        </borders>

        <cellStyleXfs count="1">
            <xf numFmtId="0" fontId="0" fillId="0" borderId="0"/>
        </cellStyleXfs>

        <cellXfs count="10">
            <!-- 0 normal -->
            <xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>

            <!-- 1 titulo principal -->
            <xf numFmtId="0" fontId="1" fillId="2" borderId="0" xfId="0" applyFont="1" applyFill="1" applyAlignment="1">
                <alignment horizontal="center" vertical="center"/>
            </xf>

            <!-- 2 encabezado oscuro -->
            <xf numFmtId="0" fontId="2" fillId="3" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1">
                <alignment horizontal="center" vertical="center"/>
            </xf>

            <!-- 3 etiqueta azul suave -->
            <xf numFmtId="0" fontId="3" fillId="4" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1">
                <alignment horizontal="center" vertical="center"/>
            </xf>

            <!-- 4 celda normal con borde, alineación izquierda -->
            <xf numFmtId="0" fontId="0" fillId="6" borderId="1" xfId="0" applyBorder="1" applyAlignment="1">
                <alignment horizontal="left" vertical="center"/>
            </xf>

            <!-- 5 moneda con borde -->
            <xf numFmtId="164" fontId="0" fillId="6" borderId="1" xfId="0" applyNumberFormat="1" applyBorder="1" applyAlignment="1">
                <alignment horizontal="center" vertical="center"/>
            </xf>

            <!-- 6 subtitulo / nota -->
            <xf numFmtId="0" fontId="4" fillId="0" borderId="0" xfId="0" applyFont="1" applyAlignment="1">
                <alignment horizontal="center" vertical="center"/>
            </xf>

            <!-- 7 fila de periodo -->
            <xf numFmtId="0" fontId="3" fillId="5" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1">
                <alignment horizontal="center" vertical="center"/>
            </xf>

            <!-- 8 valor destacado -->
            <xf numFmtId="0" fontId="5" fillId="6" borderId="1" xfId="0" applyFont="1" applyBorder="1" applyAlignment="1">
                <alignment horizontal="center" vertical="center"/>
            </xf>

            <!-- 9 celda centrada con borde -->
            <xf numFmtId="0" fontId="0" fillId="6" borderId="1" xfId="0" applyBorder="1" applyAlignment="1">
                <alignment horizontal="center" vertical="center"/>
            </xf>
        </cellXfs>
    </styleSheet>';
    }

    private function xlsxHojaResumen(array $datos): string
    {
        $filas = [
            [1, 'Estadísticas - Togas', '', ''],
            [6, 'Sistema de control - togas · Centro Profesional de Cómputo CPC', '', ''],
            [],
            [7, 'Periodo', $datos['tituloPeriodo'], '', ''],
            [7, 'Desde', $datos['fechaInicio']->format('d/m/Y'), 'Hasta', $datos['fechaFin']->format('d/m/Y')],
            [],
            [2, 'Indicador', 'Valor', 'Detalle'],
            [4, 'Alquileres registrados', $datos['alquileresRegistrados'], 'Registros no cancelados'],
            [4, 'Pagos registrados', $datos['pagosRegistrados'], 'Pagos realizados'],
            [4, 'Ingresos recibidos', 'Q' . number_format($datos['ingresosRecibidos'], 2), 'Dinero real recibido. No incluye descuentos.'],
            [4, 'Descuentos aplicados', 'Q' . number_format($datos['descuentosAplicados'], 2), 'Rebajas autorizadas registradas en pagos.'],
            [4, 'Mora generada', 'Q' . number_format($datos['moraGenerada'], 2), 'Mora por devoluciones tardías.'],
            [4, 'Saldo pendiente actual', 'Q' . number_format($datos['saldoPendienteActual'], 2), 'Saldo vivo de alquileres no cancelados.'],
            [4, 'Producto más alquilado', $datos['productoMasAlquilado']->nombre ?? 'Sin datos', 'Según cantidad alquilada.'],
            [4, 'Talla más alquilada', $datos['tallaTogaMasAlquilada']->talla ?? 'Sin datos', 'Solo productos tipo toga.'],
            [],
            [6, 'Nota: los ingresos recibidos se calculan únicamente con pagos reales registrados. Los descuentos no se consideran ingreso.', '', ''],
        ];

        return $this->crearWorksheetXml(
            $filas,
            [1 => 28, 2 => 26, 3 => 56, 4 => 20],
            ['A1:C1', 'A2:C2', 'A17:C17']
        );
    }

    private function xlsxHojaTablaResumen(array $datos): string
    {
        $filas = [
            [1, 'Tabla resumen - Estadísticas', '', '', '', '', ''],
            [6, $datos['tituloPeriodo'] . ' · ' . $datos['fechaInicio']->format('d/m/Y') . ' al ' . $datos['fechaFin']->format('d/m/Y'), '', '', '', '', ''],
            [],
            [2, 'Periodo', 'Alquileres', 'Pagos', 'Ingresos', 'Descuentos', 'Mora', 'Total aplicado'],
        ];

        foreach ($datos['tablaResumen'] as $fila) {
            $filas[] = [
                9,
                $fila['periodo'],
                $fila['alquileres'],
                $fila['pagos'],
                'Q' . number_format($fila['ingresos'], 2),
                'Q' . number_format($fila['descuentos'], 2),
                'Q' . number_format($fila['mora'], 2),
                'Q' . number_format($fila['total_aplicado'], 2),
            ];
        }

        if ($datos['tablaResumen']->count() > 0) {
            $filas[] = [];
            $filas[] = [
                3,
                'Total',
                $datos['tablaResumen']->sum('alquileres'),
                $datos['tablaResumen']->sum('pagos'),
                'Q' . number_format($datos['tablaResumen']->sum('ingresos'), 2),
                'Q' . number_format($datos['tablaResumen']->sum('descuentos'), 2),
                'Q' . number_format($datos['tablaResumen']->sum('mora'), 2),
                'Q' . number_format($datos['tablaResumen']->sum('total_aplicado'), 2),
            ];
        }

        return $this->crearWorksheetXml(
            $filas,
            [1 => 20, 2 => 16, 3 => 14, 4 => 18, 5 => 18, 6 => 18, 7 => 20],
            ['A1:G1', 'A2:G2']
        );
    }

    private function xlsxHojaRankings(array $datos): string
    {
        $filas = [
            [1, 'Rankings - Estadísticas', '', ''],
            [6, $datos['tituloPeriodo'], '', ''],
            [],
            [2, 'Indicador', 'Resultado', 'Valor', 'Comentario'],

            [
                4,
                'Día con más alquileres',
                $datos['diaMasAlquileres']->periodo ?? 'Sin datos',
                $datos['diaMasAlquileres']->total ?? 0,
                'Cantidad de alquileres registrados',
            ],

            [
                4,
                'Día con más ingresos',
                $datos['diaMasIngresos']->periodo ?? 'Sin datos',
                $datos['diaMasIngresos'] ? 'Q' . number_format($datos['diaMasIngresos']->total, 2) : 'Q0.00',
                'Ingresos reales recibidos',
            ],

            [
                4,
                'Día con más descuentos',
                $datos['diaMasDescuentos']->periodo ?? 'Sin datos',
                $datos['diaMasDescuentos'] ? 'Q' . number_format($datos['diaMasDescuentos']->total, 2) : 'Q0.00',
                'Descuentos aplicados',
            ],

            [
                4,
                'Día con más mora',
                $datos['diaMasMora']->periodo ?? 'Sin datos',
                $datos['diaMasMora'] ? 'Q' . number_format($datos['diaMasMora']->total, 2) : 'Q0.00',
                'Mora generada',
            ],

            [
                4,
                'Método de pago más usado',
                $datos['metodoPagoMasUsado']->metodo_pago ?? 'Sin datos',
                $datos['metodoPagoMasUsado']->total ?? 0,
                'Cantidad de pagos',
            ],

            [
                4,
                'Institución con más alquileres',
                $datos['institucionMasAlquileres']->institucion_representada ?? 'Sin datos',
                $datos['institucionMasAlquileres']->total ?? 0,
                'Cantidad de alquileres',
            ],
        ];

        return $this->crearWorksheetXml(
            $filas,
            [1 => 32, 2 => 38, 3 => 18, 4 => 34],
            ['A1:D1', 'A2:D2']
        );
    }

    private function xlsxHojaProductos(array $datos): string
    {
        $filas = [
            [1, 'Top productos más alquilados', '', ''],
            [6, $datos['tituloPeriodo'], '', ''],
            [],
            [2, 'Posición', 'Producto', 'Tipo', 'Cantidad'],
        ];

        foreach ($datos['topProductos'] as $index => $producto) {
            $filas[] = [
                9,
                $index + 1,
                $producto->nombre,
                $producto->tipo_producto,
                $producto->total,
            ];
        }

        if ($datos['topProductos']->count() === 0) {
            $filas[] = [9, '-', 'Sin datos', '', 0];
        }

        return $this->crearWorksheetXml(
            $filas,
            [1 => 14, 2 => 42, 3 => 18, 4 => 14],
            ['A1:D1', 'A2:D2']
        );
    }

    private function xlsxHojaTallas(array $datos): string
    {
        $filas = [
            [1, 'Top tallas de toga', '', ''],
            [6, $datos['tituloPeriodo'], '', ''],
            [],
            [2, 'Posición', 'Talla', 'Cantidad', 'Comentario'],
        ];

        foreach ($datos['topTallasToga'] as $index => $talla) {
            $filas[] = [
                9,
                $index + 1,
                'Talla ' . $talla->talla,
                $talla->total,
                'Togas alquiladas',
            ];
        }

        if ($datos['topTallasToga']->count() === 0) {
            $filas[] = [9, '-', 'Sin datos', 0, ''];
        }

        return $this->crearWorksheetXml(
            $filas,
            [1 => 14, 2 => 18, 3 => 14, 4 => 28],
            ['A1:D1', 'A2:D2']
        );
    }
    private function crearWorksheetXml(array $filas, array $anchos = [], array $mergedCells = []): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
    <worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
        <sheetViews>
            <sheetView workbookViewId="0" showGridLines="0"/>
        </sheetViews>';

        if (!empty($anchos)) {
            $xml .= '<cols>';

            foreach ($anchos as $columna => $ancho) {
                $xml .= '<col min="' . $columna . '" max="' . $columna . '" width="' . $ancho . '" customWidth="1"/>';
            }

            $xml .= '</cols>';
        }

        $xml .= '<sheetData>';

        foreach ($filas as $rowIndex => $fila) {
            $numeroFila = $rowIndex + 1;

            if (empty($fila)) {
                $xml .= '<row r="' . $numeroFila . '" ht="8" customHeight="1"></row>';
                continue;
            }

            $style = (int) array_shift($fila);
            $altura = $this->alturaFilaXlsxPorEstilo($style);

            $xml .= '<row r="' . $numeroFila . '" ht="' . $altura . '" customHeight="1">';

            foreach ($fila as $colIndex => $valor) {
                $columna = $this->numeroAColumnaExcel($colIndex + 1);
                $celda = $columna . $numeroFila;

                $styleCelda = $style;

                // Si es estilo 4, dejamos la primera columna a la izquierda
                // y centramos todas las demás.
                if ($style === 4 && $colIndex >= 1) {
                    $styleCelda = 9;
                }

                if (is_numeric($valor) && $valor !== '') {
                    $xml .= '<c r="' . $celda . '" s="' . $styleCelda . '"><v>' . $valor . '</v></c>';
                } else {
                    $valorSeguro = htmlspecialchars((string) $valor, ENT_QUOTES | ENT_XML1, 'UTF-8');
                    $xml .= '<c r="' . $celda . '" t="inlineStr" s="' . $styleCelda . '"><is><t>' . $valorSeguro . '</t></is></c>';
                }
            }

            $xml .= '</row>';
        }

        $xml .= '</sheetData>';

        if (!empty($mergedCells)) {
            $xml .= '<mergeCells count="' . count($mergedCells) . '">';

            foreach ($mergedCells as $range) {
                $xml .= '<mergeCell ref="' . $range . '"/>';
            }

            $xml .= '</mergeCells>';
        }

        $xml .= '</worksheet>';

        return $xml;
    }

    private function xcell($valor, int $style = 5): array
    {
        return ['v' => $valor, 's' => $style];
    }

    private function alturaFilaXlsxPorEstilo(int $style): int
    {
        return match ($style) {
            1 => 30, // título principal
            2 => 24, // encabezado de tabla
            6 => 22, // subtítulo / nota
            7 => 22, // datos de periodo
            default => 21,
        };
    }

    private function numeroAColumnaExcel(int $numero): string
    {
        $columna = '';

        while ($numero > 0) {
            $modulo = ($numero - 1) % 26;
            $columna = chr(65 + $modulo) . $columna;
            $numero = intdiv($numero - $modulo, 26);
        }

        return $columna;
    }

    private function obtenerDatosExportacion(Request $request): array
    {
        $tipoVista = $request->get('tipo_vista', 'mes');
        $area = $request->get('area', 'general');

        $fecha = $request->get('fecha', now()->toDateString());
        $mes = $request->get('mes', now()->format('Y-m'));
        $anio = $request->get('anio', now()->format('Y'));
        $desde = $request->get('desde');
        $hasta = $request->get('hasta');

        [$fechaInicio, $fechaFin, $agrupacion, $tituloPeriodo] = $this->resolverPeriodo(
            $tipoVista,
            $fecha,
            $mes,
            $anio,
            $desde,
            $hasta
        );

        $alquileresRegistrados = DB::table('alquileres')
            ->whereBetween('fecha_alquiler', [$fechaInicio->toDateString(), $fechaFin->toDateString()])
            ->where('estado', '!=', 'CANCELADO')
            ->count();

        $pagosRegistrados = DB::table('pagos')
            ->whereBetween(DB::raw('DATE(created_at)'), [$fechaInicio->toDateString(), $fechaFin->toDateString()])
            ->count();

        $ingresosRecibidos = DB::table('pagos')
            ->whereBetween(DB::raw('DATE(created_at)'), [$fechaInicio->toDateString(), $fechaFin->toDateString()])
            ->sum('monto');

        $descuentosAplicados = DB::table('pagos')
            ->whereBetween(DB::raw('DATE(created_at)'), [$fechaInicio->toDateString(), $fechaFin->toDateString()])
            ->sum('descuento_aplicado');

        $moraGenerada = DB::table('alquileres')
            ->whereNotNull('fecha_hora_devolucion_real')
            ->whereBetween(DB::raw('DATE(fecha_hora_devolucion_real)'), [$fechaInicio->toDateString(), $fechaFin->toDateString()])
            ->where('estado', '!=', 'CANCELADO')
            ->sum('monto_mora');

        $saldoPendienteActual = DB::table('alquileres')
            ->where('estado', '!=', 'CANCELADO')
            ->sum('saldo_pendiente');

        $productoMasAlquilado = DB::table('alquiler_detalles')
            ->join('productos', 'productos.id', '=', 'alquiler_detalles.producto_id')
            ->join('alquileres', 'alquileres.id', '=', 'alquiler_detalles.alquiler_id')
            ->whereBetween('alquileres.fecha_alquiler', [$fechaInicio->toDateString(), $fechaFin->toDateString()])
            ->where('alquileres.estado', '!=', 'CANCELADO')
            ->select(
                'productos.nombre',
                DB::raw('SUM(alquiler_detalles.cantidad) as total_alquilado')
            )
            ->groupBy('productos.id', 'productos.nombre')
            ->orderByDesc('total_alquilado')
            ->first();

        $tallaTogaMasAlquilada = DB::table('alquiler_detalles')
            ->join('productos', 'productos.id', '=', 'alquiler_detalles.producto_id')
            ->join('producto_togas', 'producto_togas.producto_id', '=', 'productos.id')
            ->join('alquileres', 'alquileres.id', '=', 'alquiler_detalles.alquiler_id')
            ->where('productos.tipo_producto', 'TOGA')
            ->whereBetween('alquileres.fecha_alquiler', [$fechaInicio->toDateString(), $fechaFin->toDateString()])
            ->where('alquileres.estado', '!=', 'CANCELADO')
            ->select(
                'producto_togas.talla',
                DB::raw('SUM(alquiler_detalles.cantidad) as total_alquilado')
            )
            ->groupBy('producto_togas.talla')
            ->orderByDesc('total_alquilado')
            ->first();

        $alquileresPorPeriodo = $this->alquileresPorPeriodo($fechaInicio, $fechaFin, $agrupacion);
        $pagosPorPeriodo = $this->pagosPorPeriodo($fechaInicio, $fechaFin, $agrupacion);
        $moraPorPeriodo = $this->moraPorPeriodo($fechaInicio, $fechaFin, $agrupacion);

        $periodos = collect()
            ->merge($alquileresPorPeriodo->pluck('periodo'))
            ->merge($pagosPorPeriodo->pluck('periodo'))
            ->merge($moraPorPeriodo->pluck('periodo'))
            ->unique()
            ->sort()
            ->values();

        $tablaResumen = $periodos->map(function ($periodo) use ($alquileresPorPeriodo, $pagosPorPeriodo, $moraPorPeriodo) {
            $alquiler = $alquileresPorPeriodo->firstWhere('periodo', $periodo);
            $pago = $pagosPorPeriodo->firstWhere('periodo', $periodo);
            $mora = $moraPorPeriodo->firstWhere('periodo', $periodo);

            $ingresos = $pago->ingresos ?? 0;
            $descuentos = $pago->descuentos ?? 0;
            $moraGenerada = $mora->mora ?? 0;

            return [
                'periodo' => $periodo,
                'alquileres' => $alquiler->total ?? 0,
                'pagos' => $pago->pagos ?? 0,
                'ingresos' => $ingresos,
                'descuentos' => $descuentos,
                'mora' => $moraGenerada,
                'total_aplicado' => $ingresos + $descuentos,
            ];
        });

        return [
            'tipoVista' => $tipoVista,
            'area' => $area,
            'fecha' => $fecha,
            'mes' => $mes,
            'anio' => $anio,
            'desde' => $desde,
            'hasta' => $hasta,
            'fechaInicio' => $fechaInicio,
            'fechaFin' => $fechaFin,
            'tituloPeriodo' => $tituloPeriodo,

            'alquileresRegistrados' => $alquileresRegistrados,
            'pagosRegistrados' => $pagosRegistrados,
            'ingresosRecibidos' => $ingresosRecibidos,
            'descuentosAplicados' => $descuentosAplicados,
            'moraGenerada' => $moraGenerada,
            'saldoPendienteActual' => $saldoPendienteActual,
            'productoMasAlquilado' => $productoMasAlquilado,
            'tallaTogaMasAlquilada' => $tallaTogaMasAlquilada,

            'tablaResumen' => $tablaResumen,

            'diaMasAlquileres' => $this->diaMasAlquileres($fechaInicio, $fechaFin),
            'diaMasIngresos' => $this->diaMasIngresos($fechaInicio, $fechaFin),
            'diaMasDescuentos' => $this->diaMasDescuentos($fechaInicio, $fechaFin),
            'diaMasMora' => $this->diaMasMora($fechaInicio, $fechaFin),
            'metodoPagoMasUsado' => $this->metodoPagoMasUsado($fechaInicio, $fechaFin),
            'institucionMasAlquileres' => $this->institucionMasAlquileres($fechaInicio, $fechaFin),
            'topProductos' => $this->topProductos($fechaInicio, $fechaFin),
            'topTallasToga' => $this->topTallasToga($fechaInicio, $fechaFin),
        ];
    }

}