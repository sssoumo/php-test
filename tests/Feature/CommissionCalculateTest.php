<?php

namespace Tests\Feature;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CommissionCalculateTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function testCommissionCalculateSuccessfulResponse(): void
    {
        // Define the expected output
        $expectedOutput = [
            0.6,
            3,
            0,
            0.06,
            1.5,
            0,
            3,
            0.3,
            0.3,
            3,
            0,
            0,
            8607.39
        ];

        $file = new UploadedFile(
            storage_path('input.csv'),
            'input.csv',
            'text/csv',
            null,
            true
        );

        // Make a POST request to the API with the CSV file
        $response = $this->post('/api/calculate-commission', [
            'csv_file' => $file,
        ]);

        // Check the response status
        $response->assertStatus(200);

        // Check the response data
        $response->assertJson([
            'fees' => $expectedOutput,
        ]);
    }
}
