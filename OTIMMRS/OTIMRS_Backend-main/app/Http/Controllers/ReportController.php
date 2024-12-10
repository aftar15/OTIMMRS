<?php

namespace App\Http\Controllers;

use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Carbon\Carbon;

class ReportController extends Controller
{
    protected $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    public function getOverview()
    {
        try {
            $stats = $this->reportService->getOverviewStats();
            
            return response()->json([
                'success' => true,
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving overview statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getAttractionStats(Request $request)
    {
        try {
            $timeframe = $request->input('timeframe', 'month');
            $stats = $this->reportService->getAttractionStats($timeframe);
            
            return response()->json([
                'success' => true,
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving attraction statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getActivityStats(Request $request)
    {
        try {
            $timeframe = $request->input('timeframe', 'month');
            $stats = $this->reportService->getActivityStats($timeframe);
            
            return response()->json([
                'success' => true,
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving activity statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getTouristStats(Request $request)
    {
        try {
            $timeframe = $request->input('timeframe', 'month');
            $stats = $this->reportService->getTouristStats($timeframe);
            
            return response()->json([
                'success' => true,
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving tourist statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function exportReport(Request $request)
    {
        try {
            $type = $request->input('type', 'overview');
            $timeframe = $request->input('timeframe', 'month');
            $format = $request->input('format', 'csv');

            // Get the appropriate data based on type
            $data = match($type) {
                'attractions' => $this->reportService->getAttractionStats($timeframe),
                'activities' => $this->reportService->getActivityStats($timeframe),
                'tourists' => $this->reportService->getTouristStats($timeframe),
                default => $this->reportService->getOverviewStats(),
            };

            // Format the data based on the requested format
            if ($format === 'json') {
                $filename = "report_{$type}_{$timeframe}_" . date('Y-m-d') . ".json";
                $content = json_encode($data, JSON_PRETTY_PRINT);
                $headers = [
                    'Content-Type' => 'application/json',
                    'Content-Disposition' => 'attachment; filename="' . $filename . '"'
                ];
            } else {
                $filename = "report_{$type}_{$timeframe}_" . date('Y-m-d') . ".csv";
                $content = $this->convertToCSV($data);
                $headers = [
                    'Content-Type' => 'text/csv',
                    'Content-Disposition' => 'attachment; filename="' . $filename . '"'
                ];
            }

            return Response::make($content, 200, $headers);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error exporting report',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function convertToCSV($data)
    {
        $output = fopen('php://temp', 'r+');
        
        // Add headers
        fputcsv($output, array_keys((array) $data));
        
        // Add data
        foreach ($data as $row) {
            if (is_array($row) || is_object($row)) {
                fputcsv($output, (array) $row);
            } else {
                fputcsv($output, [$row]);
            }
        }
        
        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);
        
        return $csv;
    }
}
