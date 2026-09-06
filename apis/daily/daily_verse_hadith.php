<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/bootstrap.php';

requireMethod('GET');

try {
    $typeParam = strtolower(trim($_GET['type'] ?? 'all'));
    $searchParam = trim($_GET['search'] ?? '');

    $ayahs = [];
    $hadiths = [];

    // 1. Fetch Ayahs if type is 'all' or 'ayah'
    if ($typeParam === 'all' || $typeParam === 'ayah' || $typeParam === 'ayahs') {
        $queryAyahs = "
            SELECT
                id,
                arabic,
                english,
                reference,
                surah,
                surah_number AS surahNumber,
                ayah_number AS ayahNumber,
                topic,
                sort_order AS sortOrder
            FROM daily_verse_hadith_ayahs
        ";

        $conditions = [];
        $params = [];
        $types = '';

        if (!empty($searchParam)) {
            $searchTerm = '%' . $searchParam . '%';
            $conditions[] = "(arabic LIKE ? OR english LIKE ? OR reference LIKE ? OR surah LIKE ? OR topic LIKE ?)";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $types .= 'sssss';
        }

        if (!empty($conditions)) {
            $queryAyahs .= " WHERE " . implode(" AND ", $conditions);
        }

        $queryAyahs .= " ORDER BY sort_order ASC, id ASC";

        if (!empty($params)) {
            $stmt = $conn->prepare($queryAyahs);
            if (!$stmt) {
                throw new Exception("Database prepare error: " . $conn->error);
            }
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $result = $stmt->get_result();
            $itemsRaw = $result ? fetchAll($result) : [];
            $stmt->close();
        } else {
            $result = $conn->query($queryAyahs);
            $itemsRaw = $result ? fetchAll($result) : [];
        }

        foreach ($itemsRaw as $row) {
            $ayahs[] = [
                'id' => (int) ($row['id'] ?? 0),
                'arabic' => $row['arabic'] ?? '',
                'english' => $row['english'] ?? '',
                'reference' => $row['reference'] ?? '',
                'surah' => $row['surah'] ?? '',
                'surahNumber' => (int) ($row['surahNumber'] ?? 0),
                'ayahNumber' => (int) ($row['ayahNumber'] ?? 0),
                'topic' => $row['topic'] ?? ''
            ];
        }
    }

    // 2. Fetch Hadiths if type is 'all' or 'hadith'
    if ($typeParam === 'all' || $typeParam === 'hadith' || $typeParam === 'hadiths') {
        $queryHadiths = "
            SELECT
                id,
                arabic,
                english,
                reference,
                source,
                narrator,
                topic,
                sort_order AS sortOrder
            FROM daily_verse_hadith_hadiths
        ";

        $conditions = [];
        $params = [];
        $types = '';

        if (!empty($searchParam)) {
            $searchTerm = '%' . $searchParam . '%';
            $conditions[] = "(arabic LIKE ? OR english LIKE ? OR reference LIKE ? OR source LIKE ? OR narrator LIKE ? OR topic LIKE ?)";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $types .= 'ssssss';
        }

        if (!empty($conditions)) {
            $queryHadiths .= " WHERE " . implode(" AND ", $conditions);
        }

        $queryHadiths .= " ORDER BY sort_order ASC, id ASC";

        if (!empty($params)) {
            $stmt = $conn->prepare($queryHadiths);
            if (!$stmt) {
                throw new Exception("Database prepare error: " . $conn->error);
            }
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $result = $stmt->get_result();
            $itemsRaw = $result ? fetchAll($result) : [];
            $stmt->close();
        } else {
            $result = $conn->query($queryHadiths);
            $itemsRaw = $result ? fetchAll($result) : [];
        }

        foreach ($itemsRaw as $row) {
            $hadiths[] = [
                'id' => (int) ($row['id'] ?? 0),
                'arabic' => $row['arabic'] ?? '',
                'english' => $row['english'] ?? '',
                'reference' => $row['reference'] ?? '',
                'source' => $row['source'] ?? '',
                'narrator' => $row['narrator'] ?? '',
                'topic' => $row['topic'] ?? ''
            ];
        }
    }

    jsonResponse(
        true,
        [
            'total_ayahs' => count($ayahs),
            'total_hadiths' => count($hadiths),
            'ayahs' => $ayahs,
            'hadiths' => $hadiths
        ],
        message: 'Successfully retrieved daily inspirations'
    );
} catch (mysqli_sql_exception $e) {
    error_log($e->getMessage());

    jsonResponse(
        false,
        [],
        'Failed to retrieve daily inspirations: ' . $e->getMessage(),
        500
    );
} catch (Exception $e) {
    error_log($e->getMessage());

    jsonResponse(
        false,
        [],
        'An unexpected error occurred: ' . $e->getMessage(),
        500
    );
}