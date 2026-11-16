<?php

namespace Tests\Feature;

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Route;
use PDOException;
use Tests\TestCase;

class RawSqlExceptionHandlingTest extends TestCase
{
    /**
     * When app.debug is false, a QueryException in a JSON request must return a generic 500
     * and must never leak SQLSTATE or raw SQL text.
     */
    public function test_query_exception_returns_clean_500_without_sqlstate_when_debug_is_false(): void
    {
        config(['app.debug' => false]);

        Route::get('/test-query-exception-qa', function () {
            throw new QueryException(
                'mysql',
                'SELECT * FROM secret_table_internal WHERE sensitive_col = 1',
                [],
                new PDOException('SQLSTATE[42S02]: Base table or view not found: 1146 Table secret_table_internal does not exist')
            );
        });

        $response = $this->getJson('/test-query-exception-qa');

        $response->assertStatus(500);
        $response->assertExactJson([
            'message' => 'Server error. Please try again.',
        ]);

        $content = $response->getContent();
        $this->assertStringNotContainsString('SQLSTATE', $content);
        $this->assertStringNotContainsString('secret_table_internal', $content);
        $this->assertStringNotContainsString('sensitive_col', $content);
    }

    /**
     * When app.debug is false, a PDOException in a JSON request must return a generic 500
     * and must never leak SQLSTATE or connection credentials.
     */
    public function test_pdo_exception_returns_clean_500_without_sqlstate_when_debug_is_false(): void
    {
        config(['app.debug' => false]);

        Route::get('/test-pdo-exception-qa', function () {
            throw new PDOException('SQLSTATE[HY000] [2002] Connection refused to 127.0.0.1:3306');
        });

        $response = $this->getJson('/test-pdo-exception-qa');

        $response->assertStatus(500);
        $response->assertExactJson([
            'message' => 'Server error. Please try again.',
        ]);

        $content = $response->getContent();
        $this->assertStringNotContainsString('SQLSTATE', $content);
        $this->assertStringNotContainsString('Connection refused', $content);
        $this->assertStringNotContainsString('127.0.0.1', $content);
    }
}
