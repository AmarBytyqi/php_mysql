<?php
    //phpinfo()

    $x = 5;
    $name = "Hello";
    $number = 3.14159;
    echo gettype($x);
    echo "<br>";
    echo gettype($name);
    echo "<br>";
    echo gettype($number);

    function addNumbers($num1, $num2) {
        $sum = $num1 + $num2;
        echo "<br>The sum of $num1 and $num2 is $sum";
    }
    
    addNumbers(10, 5);
    addNumbers(7634579034598347589034589, 37647832478235491284356);


    function product($n1, $n2){
        return $n1 * $n2;
    }
    $sum3 = product(5,8);
    echo "<br>$sum3";

    function maxResult($num1, $num2) {
        if ($num1 > $num2) {
            return $num1;
        } else {
            return $num2;
        }
    }
    
    $result_max = maxResult(5, 3); 
    echo "<br>";
    echo "The bigger number between 5 and 3 is $result_max";
     
?>