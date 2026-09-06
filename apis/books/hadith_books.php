<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/bootstrap.php';

requireMethod('GET');

try {
    $result = $conn->query("
        SELECT
            id,
            length,
            arabic_title AS arabicTitle,
            arabic_author AS arabicAuthor,
            english_title AS englishTitle,
            english_author AS englishAuthor,
            short_description AS shortDescription,
            long_description AS longDescription
        FROM hadith_books
        ORDER BY id ASC
    ");

    if (!$result) {
        jsonResponse(
            false,
            [],
            "Failed to retrieve Hadith books data",
            500
        );
    }

    $books = fetchAll($result);

    jsonResponse(
        true,
        [
            "books" => $books
        ],
        message: "Successfully retrieved Hadith books data"
    );
} catch (mysqli_sql_exception $e) {
    error_log($e->getMessage());

    jsonResponse(
        false,
        [],
        "Failed to retrieve Hadith books data",
        500
    );
}
