<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Recibo {{ $alquiler->codigo_recibo }}</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            color: #111827;
            margin: 0;
            padding: 20px;
            background: #f3f4f6;
            font-size: 13px;
        }

        .receipt-container {
            max-width: 850px;
            margin: 0 auto;
            background: #ffffff;
            padding: 28px;
            border: 1px solid #d1d5db;
        }

        .header {
            display: flex;
            justify-content: space-between;
            gap: 20px;
            border-bottom: 2px solid #111827;
            padding-bottom: 16px;
            margin-bottom: 18px;
        }

        .brand h1 {
            margin: 0;
            font-size: 22px;
            letter-spacing: 0.5px;
        }

        .brand p {
            margin: 4px 0;
            color: #4b5563;
        }

        .receipt-box {
            text-align: right;
        }

        .receipt-box .label {
            color: #6b7280;
            font-size: 12px;
            text-transform: uppercase;
        }

        .receipt-box .code {
            font-size: 20px;
            font-weight: bold;
            margin-top: 4px;
        }

        .status-row {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 18px;
        }

        .status-card {
            flex: 1;
            border: 1px solid #e5e7eb;
            padding: 10px;
            background: #f9fafb;
        }

        .status-card strong {
            display: block;
            font-size: 12px;
            color: #6b7280;
            margin-bottom: 4px;
        }

        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: bold;
            border: 1px solid #d1d5db;
        }

        .badge-success {
            background: #dcfce7;
            color: #166534;
            border-color: #86efac;
        }

        .badge-warning {
            background: #fef3c7;
            color: #92400e;
            border-color: #fcd34d;
        }

        .badge-danger {
            background: #fee2e2;
            color: #991b1b;
            border-color: #fca5a5;
        }

        .badge-info {
            background: #dbeafe;
            color: #1e40af;
            border-color: #93c5fd;
        }

        .badge-muted {
            background: #f3f4f6;
            color: #374151;
        }

        .section {
            margin-top: 18px;
        }

        .section-title {
            font-size: 15px;
            font-weight: bold;
            margin-bottom: 8px;
            padding-bottom: 5px;
            border-bottom: 1px solid #e5e7eb;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px 18px;
        }

        .field strong {
            display: block;
            color: #6b7280;
            font-size: 12px;
            margin-bottom: 2px;
        }

        .field span {
            color: #111827;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }

        th {
            background: #111827;
            color: #ffffff;
            text-align: left;
            padding: 8px;
            font-size: 12px;
        }

        td {
            border: 1px solid #e5e7eb;
            padding: 8px;
            vertical-align: top;
        }

        .text-right {
            text-align: right;
        }

        .totals {
            width: 330px;
            margin-left: auto;
            margin-top: 14px;
        }

        .totals-row {
            display: flex;
            justify-content: space-between;
            border-bottom: 1px solid #e5e7eb;
            padding: 7px 0;
        }

        .totals-row.total {
            font-size: 16px;
            font-weight: bold;
            border-bottom: 2px solid #111827;
        }

        .totals-row.balance {
            font-weight: bold;
        }

        .observations {
            min-height: 45px;
            border: 1px solid #e5e7eb;
            padding: 10px;
            background: #f9fafb;
            white-space: pre-wrap;
        }

        .signatures {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 50px;
            margin-top: 60px;
        }

        .signature-line {
            border-top: 1px solid #111827;
            text-align: center;
            padding-top: 8px;
            font-size: 12px;
        }

        .footer {
            margin-top: 28px;
            padding-top: 12px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            color: #6b7280;
            font-size: 11px;
        }

        .actions {
            max-width: 850px;
            margin: 14px auto;
            display: flex;
            justify-content: flex-end;
            gap: 8px;
        }

        .btn {
            border: none;
            padding: 9px 14px;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            font-size: 13px;
            display: inline-block;
        }

        .btn-primary {
            background: #2563eb;
            color: white;
        }

        .btn-secondary {
            background: #6b7280;
            color: white;
        }

        @media print {
            body {
                background: #ffffff;
                padding: 0;
            }

            .receipt-container {
                max-width: 100%;
                border: none;
                padding: 0;
            }

            .actions {
                display: none;
            }

            @page {
                size: letter;
                margin: 15mm;
            }
        }
    </style>
</head>
<body>

    <div class="actions">
        <a href="{{ route('alquileres.show', $alquiler->id) }}" class="btn btn-secondary">
            Volver
        </a>

        <button onclick="window.print()" class="btn btn-primary">
            Imprimir recibo
        </button>
    </div>

    <div class="receipt-container">

        <div class="header">
            <div class="brand">
                <h1>Centro Profesional de Cómputo CPC</h1>
                <p>Sistema de control de alquiler de togas y accesorios</p>
                <p>Recibo de alquiler</p>
            </div>

            <div class="receipt-box">
                <div class="label">Código de recibo</div>
                <div class="code">{{ $alquiler->codigo_recibo }}</div>

                <div style="margin-top: 8px;">
                    <span class="label">Fecha de emisión</span><br>
                    {{ now()->format('d/m/Y H:i') }}
                </div>
            </div>
        </div>

        <div class="status-row">
            <div class="status-card">
                <strong>Estado del alquiler</strong>

                @if ($alquiler->estado === 'RESERVADO')
                    <span class="badge badge-muted">RESERVADO</span>
                @elseif ($alquiler->estado === 'ENTREGADO')
                    <span class="badge badge-info">ENTREGADO</span>
                @elseif ($alquiler->estado === 'DEVUELTO')
                    <span class="badge badge-success">DEVUELTO</span>
                @elseif ($alquiler->estado === 'CANCELADO')
                    <span class="badge badge-danger">CANCELADO</span>
                @else
                    <span class="badge badge-muted">{{ $alquiler->estado }}</span>
                @endif
            </div>

            <div class="status-card">
                <strong>Estado de pago</strong>

                @if ($alquiler->estado === 'CANCELADO')
                    <span class="badge bg-secondary">NO APLICA</span>
                @elseif ($alquiler->estado_pago === 'PENDIENTE')
                    <span class="badge bg-danger">PENDIENTE</span>
                @elseif ($alquiler->estado_pago === 'PARCIAL')
                    <span class="badge bg-warning text-dark">PARCIAL</span>
                @elseif ($alquiler->estado_pago === 'PAGADO')
                    <span class="badge bg-success">PAGADO</span>
                @else
                    <span class="badge bg-dark">{{ $alquiler->estado_pago }}</span>
                @endif
            </div>

            <div class="status-card">
                <strong>Saldo pendiente</strong>
                Q{{ number_format($alquiler->saldo_pendiente, 2) }}
            </div>
        </div>

        <div class="section">
            <div class="section-title">Datos del cliente</div>

            <div class="grid">
                <div class="field">
                    <strong>Nombre completo</strong>
                    <span>
                        {{ $alquiler->cliente->nombres ?? 'Sin cliente' }}
                        {{ $alquiler->cliente->apellidos ?? '' }}
                    </span>
                </div>

                <div class="field">
                    <strong>DPI</strong>
                    <span>{{ $alquiler->cliente->dpi ?? 'N/A' }}</span>
                </div>

                <div class="field">
                    <strong>Teléfono</strong>
                    <span>{{ $alquiler->cliente->telefono ?? 'N/A' }}</span>
                </div>

                <div class="field">
                    <strong>Dirección</strong>
                    <span>{{ $alquiler->cliente->direccion ?? 'N/A' }}</span>
                </div>
            </div>
        </div>

        <div class="section">
            <div class="section-title">Fechas del alquiler</div>

            <div class="grid">
                <div class="field">
                    <strong>Fecha de alquiler</strong>
                    <span>{{ $alquiler->fecha_alquiler ? \Carbon\Carbon::parse($alquiler->fecha_alquiler)->format('d/m/Y') : 'N/A' }}</span>
                </div>

                <div class="field">
                    <strong>Fecha de entrega</strong>
                    <span>{{ $alquiler->fecha_entrega ? \Carbon\Carbon::parse($alquiler->fecha_entrega)->format('d/m/Y') : 'N/A' }}</span>
                </div>

                <div class="field">
                    <strong>Devolución programada</strong>
                    <span>{{ $alquiler->fecha_devolucion_programada ? \Carbon\Carbon::parse($alquiler->fecha_devolucion_programada)->format('d/m/Y') : 'N/A' }}</span>
                </div>

                <div class="field">
                    <strong>Devolución real</strong>
                    <span>{{ $alquiler->fecha_devolucion_real ? \Carbon\Carbon::parse($alquiler->fecha_devolucion_real)->format('d/m/Y') : 'Pendiente' }}</span>
                </div>
            </div>
        </div>

        <div class="section">
            <div class="section-title">Productos alquilados</div>

            <table>
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Producto</th>
                        <th class="text-right">Cantidad</th>
                        <th class="text-right">Precio unitario</th>
                        <th class="text-right">Subtotal</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($alquiler->detalles as $detalle)
                        <tr>
                            <td>{{ $detalle->producto->codigo ?? 'N/A' }}</td>
                            <td>{{ $detalle->producto->nombre ?? 'Producto eliminado' }}</td>
                            <td class="text-right">{{ $detalle->cantidad }}</td>
                            <td class="text-right">Q{{ number_format($detalle->precio_unitario, 2) }}</td>
                            <td class="text-right">Q{{ number_format($detalle->subtotal, 2) }}</td>
                        </tr>

                        @if ($detalle->accesorios && $detalle->accesorios->count() > 0)
                            @foreach ($detalle->accesorios as $accesorio)
                                <tr>
                                    <td>{{ $accesorio->producto->codigo ?? 'N/A' }}</td>

                                    <td>
                                        <span style="padding-left: 18px;">
                                            ↳
                                            @if ($accesorio->tipo_cobro === 'EXTRA')
                                                Extra:
                                            @else
                                                Incluye:
                                            @endif

                                            {{ $accesorio->producto->nombre ?? 'Accesorio eliminado' }}
                                        </span>

                                        <br>

                                        <span style="padding-left: 32px; color: #6b7280; font-size: 12px;">
                                            {{ $accesorio->tipo_accesorio }}
                                            |
                                            {{ $accesorio->tipo_cobro }}
                                        </span>
                                    </td>

                                    <td class="text-right">{{ $accesorio->cantidad }}</td>
                                    <td class="text-right">Q{{ number_format($accesorio->precio_unitario, 2) }}</td>
                                    <td class="text-right">Q{{ number_format($accesorio->total_linea, 2) }}</td>
                                </tr>
                            @endforeach
                        @endif
                    @endforeach
                </tbody>
            </table>

            <div class="totals">
                <div class="totals-row">
                    <span>Subtotal</span>
                    <span>Q{{ number_format($alquiler->subtotal, 2) }}</span>
                </div>

                <div class="totals-row">
                    <span>Descuento</span>
                    <span>Q{{ number_format($alquiler->descuento, 2) }}</span>
                </div>

                <div class="totals-row total">
                    <span>Total</span>
                    <span>Q{{ number_format($alquiler->total, 2) }}</span>
                </div>

                <div class="totals-row balance">
                    <span>Saldo pendiente</span>
                    <span>Q{{ number_format($alquiler->saldo_pendiente, 2) }}</span>
                </div>
            </div>

            @php
                $subtotalTogas = $alquiler->detalles->sum('subtotal');

                $subtotalExtras = $alquiler->detalles->sum(function ($detalle) {
                    return $detalle->accesorios
                        ? $detalle->accesorios->where('tipo_cobro', 'EXTRA')->sum('total_linea')
                        : 0;
                });
            @endphp

            <div class="totals-row">
                <span>Subtotal togas</span>
                <span>Q{{ number_format($subtotalTogas, 2) }}</span>
            </div>

            <div class="totals-row">
                <span>Extras cobrables</span>
                <span>Q{{ number_format($subtotalExtras, 2) }}</span>
            </div>

        </div>

        <div class="section">
            <div class="section-title">Pagos registrados</div>

            @if ($alquiler->pagos->count() > 0)
                <table>
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Método</th>
                            <th>Referencia</th>
                            <th class="text-right">Monto</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($alquiler->pagos as $pago)
                            <tr>
                                <td>{{ $pago->created_at ? $pago->created_at->format('d/m/Y H:i') : 'N/A' }}</td>
                                <td>{{ $pago->metodo_pago }}</td>
                                <td>{{ $pago->referencia ?? 'N/A' }}</td>
                                <td class="text-right">Q{{ number_format($pago->monto, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p>No hay pagos registrados para este alquiler.</p>
            @endif
        </div>

        <div class="section">
            <div class="section-title">Observaciones</div>

            <div class="observations">
{{ $alquiler->observaciones ?? 'Sin observaciones.' }}
            </div>
        </div>

        <div class="signatures">
            <div class="signature-line">
                Firma del cliente
            </div>

            <div class="signature-line">
                Firma / sello CPC
            </div>
        </div>

        <div class="footer">
            Este documento es un comprobante interno del Centro Profesional de Cómputo CPC.
            <br>
            Generado por el Sistema de control de alquiler de togas y accesorios.
        </div>

    </div>

</body>
</html>