<?php

namespace Sukohi\EloquentExport;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\ServiceProvider;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EloquentExportServiceProvider extends ServiceProvider {

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        function getExtension($filename) {

            return pathinfo($filename, PATHINFO_EXTENSION);

        };

        function getRowData($item, $filters) {

            $row_data = [];

            if(empty($filters)) {

                $row_data = array_values($item->toArray());

            } else {

                foreach($filters as $filter) {

                    if(is_callable($filter)) {

                        $row_data[] = $filter($item);

                    }

                }

            }

            return $row_data;

        }

        Collection::macro('export', function($filename = '', $filters = [], $encoding = 'UTF-8') {

            $extension = getExtension($filename);

            if($extension === 'csv') {

                $fluent = \FluentCsv::setEncoding($encoding);

                $this->each(function($item) use($fluent, $filters) {

                    $row_data = getRowData($item, $filters);
                    $fluent->addData($row_data);

                });

                return $fluent->download($filename);

            } else if(in_array($extension, ['xls', 'xlsx'])) {

                $spreadsheet = new Spreadsheet();
                $sheet = $spreadsheet->getActiveSheet();
                $excel_data = [];

                $this->each(function($item) use(&$excel_data, $filters) {

                    $excel_data[] = getRowData($item, $filters);

                });
                $sheet->fromArray($excel_data, null, 'A1');

                $callback = function() use($spreadsheet) {

                    $writer = new Xlsx($spreadsheet);
                    $writer->save('php://output');

                };
                $status = 200;
                $headers = [
                    'Content-Type' => 'application/vnd.ms-excel',
                    'Content-Disposition' => 'attachment;filename="'. $filename .'"',
                    'Cache-Control' => 'max-age=0',
                ];
                return new StreamedResponse($callback, $status, $headers);

            } else {

                throw new \Exception('The extension must be one of the following: ".csv", ".xls", ".xlsx"');

            }

        });
    }

}
