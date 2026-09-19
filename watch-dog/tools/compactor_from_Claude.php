<?php

/**
 * Compacts an array of stdClass objects by grouping on r_country.
 * - user_id becomes the max user_id within the group
 * - count becomes the sum of count within the group
 *
 * @param array $data Array of stdClass objects with r_country, user_id, count
 * @return array Compacted array of stdClass objects
[0] => stdClass Object(
            [r_country] => Sweden
            [user_id] => 1
            [count] => 61),
[1] => stdClass Object(
            [r_country] => Spain
            [user_id] => 2
            [count] => 9);
 */
function compactByCountry(array $data): array {
    $grouped = [];
    foreach ($data as $item) {
        $key = $item->r_country;
        if (!isset($grouped[$key])) {
            $grouped[$key] = clone $item;
        } else {
            $grouped[$key]->user_id = max($grouped[$key]->user_id, $item->user_id);
            $grouped[$key]->count  += $item->count;
        }
    }
    return array_values($grouped);
}

// ---- Example usage ----

$input = [];

$o1 = new stdClass();
$o1->r_country = 'Sweden';
$o1->user_id = 1;
$o1->count = 61;
$input[] = $o1;

$o2 = new stdClass();
$o2->r_country = 'Sweden';
$o2->user_id = 2;
$o2->count = 9;
$input[] = $o2;

$result = compactByCountry($input);

print_r($result);
```

**Output:**
```
Array
(
    [0] => stdClass Object
        (
            [r_country] => Sweden
            [user_id] => 2
            [count] => 70
        )

)
```

A few notes:

- It groups by `r_country`, so if you had a third object with `r_country => "Norway"`, it would end up as a separate entry in the result — the function isn't hardcoded to only handle Sweden.
- I used `clone $item` for the first occurrence of each country so the original input array isn't mutated by reference.
- `array_values()` at the end resets the keys to `0, 1, 2...` since the intermediate `$grouped` array is keyed by country name.

If your real data has more fields you want preserved/summed differently (e.g. some fields should just take the "first" value, others should be summed, others maxed), let me know the field list and I can adjust the merge logic accordingly.
