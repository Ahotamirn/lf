<?php
/**
 * Created by PhpStorm.
 * User: Matt
 * Date: 6/21/2017
 * Time: 8:40 PM
 *
 * Output the top 20 most frequently results of a trip (i.e. “carp” or “carp and shark” or “carp,
 * shark and perch”). List the fish caught along with the number of times that result has been
 * observed on a trip (i.e. “carp, shark and perch” caught 132 times).
 *
 * Written in a single PHP file for simplicity's sake. Given a harder JSON response, the file could be
 * broken into MVC easily.
 */

$fishingTripData = getFishingTripData('https://liquid.fish/fishes.json');
$trips = processFishingData($fishingTripData);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Question 2</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css"
          integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
</head>
<body>

<div class="container">
    <table class="table col-offset-md-1 col-md-10">
        <tr>
            <th>Date</th>
            <th>Frequency</th>
            <th>Total</th>
        </tr>
        <?php foreach ($trips as $trip): ?>
            <?php $arr = array_count_values($trip['fish_caught']); ?>
            <?php arsort($arr); ?>
            <tr>
                <td><?php echo $trip['formatted_date']; ?></td>
                <td><?php echo frequencyDecorator(array_keys($arr)); ?></td>
                <td><?php echo array_sum($arr); ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>

</body>
</html>

<?php

/**
 * Get raw fishing trip data from URL.
 *
 * @param string $url
 * @return array
 */
function getFishingTripData($url)
{
    $ch = curl_init();

    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
    ]);

    $output = curl_exec($ch);
    curl_close($ch);

    return json_decode($output, true);
}

/**
 * Return aggregated data from each fishing trip. Fishing trips are taken and aggregated daily (lucky).
 *
 * @param array $fishingTripData
 * @return array
 */
function processFishingData($fishingTripData)
{
    $trips = array();
    foreach ($fishingTripData as $datum) {
        $date = new DateTime($datum['date']);
        $key = $date->format('ymd');

        if (!isset($trips[$key])) {
            $trips[$key] = [
                'formatted_date' => $date->format('m/d/Y'),
                'fish_caught' => [],
            ];
        }

        foreach ($datum['fish_caught'] as $fish) {
            $trips[$key]['fish_caught'][] = $fish;
        }
    }

    return $trips;
}

/**
 * @param array $array
 * @return string
 */
function frequencyDecorator($array)
{
    // get keys first 20 elements
    $array = array_slice($array, 0, 20);

    // transform array to comma sperated string with 'and' before last element
    $last  = array_slice($array, -1);
    $first = join(', ', array_slice($array, 0, -1));
    $both  = array_filter(array_merge(array($first), $last), 'strlen');
    return join(' and ', $both);
}

?>