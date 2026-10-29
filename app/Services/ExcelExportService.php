<?php

namespace App\Services;

use Symfony\Component\HttpFoundation\Response;

class ExcelExportService
{
    /**
     * Generate dan unduh file Excel (.xls) berformat tabel rapi dan kompatibel penuh dengan Microsoft Excel.
     */
    public static function download(string $filename, string $title, array $headers, array $rows): Response
    {
        $cleanFilename = str_ends_with($filename, '.xls') ? $filename : $filename . '.xls';

        $output = '<!DOCTYPE html>';
        $output .= '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
        $output .= '<head>';
        $output .= '<meta http-equiv="Content-Type" content="text/html; charset=utf-8">';
        $output .= '<!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name>' . htmlspecialchars(substr($title, 0, 31)) . '</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]-->';
        $output .= '<style>';
        $output .= 'body { font-family: Calibri, Arial, sans-serif; font-size: 11pt; }';
        $output .= '.title { font-size: 16pt; font-weight: bold; color: #1e3a8a; margin-bottom: 4px; }';
        $output .= '.meta { font-size: 9pt; color: #64748b; margin-bottom: 12px; }';
        $output .= 'table { border-collapse: collapse; width: 100%; margin-top: 10px; }';
        $output .= 'th { background-color: #1e40af; color: #ffffff; font-weight: bold; text-align: left; padding: 10px 8px; border: 1px solid #cbd5e1; font-size: 11pt; }';
        $output .= 'td { padding: 8px; border: 1px solid #e2e8f0; font-size: 10pt; vertical-align: top; }';
        $output .= 'tr:nth-child(even) td { background-color: #f8fafc; }';
        $output .= '.text-cell { mso-number-format: "\@"; }'; // Mencegah konversi nomor panjang (KTP, HP, RM) ke scientific notation
        $output .= '.number-cell { mso-number-format: "#,##0"; text-align: right; }';
        $output .= '.center-cell { text-align: center; }';
        $output .= '</style>';
        $output .= '</head>';
        $output .= '<body>';
        $output .= '<div class="title">' . htmlspecialchars($title) . '</div>';
        $output .= '<div class="meta">Diekspor pada: ' . now()->translatedFormat('d F Y, H:i:s') . ' WIB</div>';
        $output .= '<table>';
        $output .= '<thead><tr>';
        foreach ($headers as $header) {
            $output .= '<th>' . htmlspecialchars($header) . '</th>';
        }
        $output .= '</tr></thead>';
        $output .= '<tbody>';

        foreach ($rows as $row) {
            $output .= '<tr>';
            foreach ($row as $cell) {
                $val = (string) $cell;
                $isLongNumeric = is_numeric($val) && strlen($val) >= 8;
                $class = $isLongNumeric ? 'text-cell' : '';
                $output .= '<td class="' . $class . '">' . htmlspecialchars($val) . '</td>';
            }
            $output .= '</tr>';
        }

        $output .= '</tbody></table>';
        $output .= '</body></html>';

        return response($output, 200, [
            'Content-Type' => 'application/vnd.ms-excel; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="' . $cleanFilename . '"',
            'Cache-Control' => 'max-age=0, no-cache, must-revalidate, proxy-revalidate',
            'Pragma' => 'public',
        ]);
    }
}
