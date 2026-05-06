<?php
/**
 * Job Matcher - Job Matching Engine
 * Compares user skills vs job required_skills and returns a sorted match list.
 */

function getMatchedJobs(mysqli $conn, array $userSkills): array {
    if (empty($userSkills)) return [];

    $matched = [];
    $result  = $conn->query("SELECT * FROM jobs ORDER BY id ASC");

    if (!$result) return [];

    while ($job = $result->fetch_assoc()) {
        $reqSkills  = array_map('trim', explode(',', $job['required_skills']));
        $matchCount = 0;
        $matchedList = [];

        foreach ($reqSkills as $req) {
            foreach ($userSkills as $user) {
                if (strcasecmp($req, $user) === 0) {
                    $matchCount++;
                    $matchedList[] = $req;
                    break;
                }
            }
        }

        $percent = (count($reqSkills) > 0)
            ? (int) round(($matchCount / count($reqSkills)) * 100)
            : 0;

        if ($percent > 0) {
            $job['match_percent']    = $percent;
            $job['matched_skills']   = $matchedList;
            $job['missing_skills']   = array_diff($reqSkills, $matchedList);
            $matched[] = $job;
        }
    }

    // Sort: highest match first
    usort($matched, fn($a, $b) => $b['match_percent'] - $a['match_percent']);
    return $matched;
}
?>
