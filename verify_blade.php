<?php
$f = file_get_contents("resources/views/admin/salesplan/index.blade.php");

// Basic logic to find what opened what
preg_match_all('/@(if|foreach|while|for|switch|endif|endforeach|endwhile|endfor|endswitch)\b/i', $f, $matches, PREG_OFFSET_CAPTURE);

$stack = [];
$lines = explode("\n", $f);
$offsets = [];
$currentOffset = 0;
foreach($lines as $i => $l) {
    if(empty($l)) {
        $currentOffset += 1;
        continue;
    }
    for($j=0; $j<strlen($l); $j++) {
        $offsets[$currentOffset] = $i+1;
        $currentOffset++;
    }
    $offsets[$currentOffset] = $i+1;
    $currentOffset++; 
}

foreach($matches[1] as $m) {
    $tag = strtolower($m[0]);
    $offset = $m[1];
    $line = isset($offsets[$offset]) ? $offsets[$offset] : "?";

    if(in_array($tag, ['if', 'foreach', 'while', 'for', 'switch'])) {
        $stack[] = [$tag, $line];
    } else {
        $pop = array_pop($stack);
        if(!$pop) {
             echo "Mismatch: found @$tag on line $line but stack is empty\n";
        } elseif("end" . $pop[0] !== $tag) {
             echo "Mismatch: expected @end{$pop[0]} (opened on line {$pop[1]}), but found @$tag on line $line\n";
        }
    }
}

if(!empty($stack)) {
    echo "Unclosed tags:\n";
    print_r($stack);
} else {
    echo "All matched.\n";
}
