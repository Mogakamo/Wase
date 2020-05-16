<?php
// Has the form been submitted?
try {
        require ('connect.php'); // Connect to the database
        $errors = array(); // Initialize an error array.
        // --------------------check the entries-------------
        //Is the title present? If it is, sanitize it                               
        
        // Trim the first name
        $first_name =
                filter_var( $_POST['first_name'], FILTER_SANITIZE_STRING);
                if ((!empty($first_name)) && (preg_match('/[a-z\s]/i',$first_name)) &&
                (strlen($first_name) <= 30)) {
                //Sanitize the trimmed first name
                $first_nametrim = $first_name;
        }else{
                $errors[] =
        'First name missing or not alphabetic and space characters. Max 30';
        }
        //Is the last name present? If it is, sanitize it
        $last_name = filter_var( $_POST['last_name'], FILTER_SANITIZE_STRING);
        if ((!empty($last_name)) &&
                (preg_match('/[a-z\-\s\']/i',$last_name)) &&
                        (strlen($last_name) <= 40)) {
                //Sanitize the trimmed last name
                $last_nametrim = $last_name;
        }else{
                $errors[] =
        'Last name missing or not alphabetic, dash, quote or space. Max 30.';
        }
        // Check that an email address has been entered
        $emailtrim = filter_var( $_POST['email'], FILTER_SANITIZE_EMAIL);
        if ((empty($emailtrim)) ||
                (!filter_var($emailtrim, FILTER_VALIDATE_EMAIL))
                        || (strlen($emailtrim > 60))) {
                $errors[] = 'You forgot to enter your email address';
                $errors[] = ' or the e-mail format is incorrect.';
                }
        // Check for a password and match against the confirmed password:           #2
        $password1trim =
                filter_var( $_POST['password1'], FILTER_SANITIZE_STRING);
                $string_length = strlen($password1trim);
                if (empty($password1trim)){
                $errors[] ='Please enter a valid password';
        }
        else {
        if(!preg_match(
'/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[#$@!%&*?])[A-Za-z\d#$@!%&*?]{8,12}$/',
$password1trim)) {
        $errors[] =
'Invalid password, 8 to 12 chars, 1 upper, 1 lower, 1 number, 1 special.';
        } else
        {
                $password2trim =
                filter_var( $_POST['password2'], FILTER_SANITIZE_STRING);
        if($password1trim === $password2trim) {
                $password = $password1trim;
        }else{
                $errors[] = 'Your two passwords do not match.';
                $errors[] = 'Please try again';
                }
        }
        }
        //Is the 1st address present? If it is, sanitize it
        $address1 = filter_var( $_POST['address1'], FILTER_SANITIZE_STRING);
        if ((!empty($address1)) &&
                (preg_match('/[a-z0-9\.\s\,\-]/i', $address1)) &&
                        (strlen($address1) <= 30)) {
                //Sanitize the trimmed 1st address
                $address1trim = $address1;
        }else{
        $errors[] =
'Missing address. Numeric, alphabetic, period, comma, dash and space.Max 30.';
        }
        //If the 2nd address is present? If it is, sanitize it
        $address2 = filter_var( $_POST['address2'], FILTER_SANITIZE_STRING);
        if ((!empty($address2)) &&
                (preg_match('/[a-z0-9\.\s\,\-]/i', $address2)) &&
                        (strlen($address2) <= 30)) {
                                //Sanitize the trimmed 2nd address
                $address2trim = $address2;
        }else{
                $address2trim = NULL;
        }
        //Is the city present? If it is, sanitize it
        $city = filter_var( $_POST['city'], FILTER_SANITIZE_STRING);
        if ((!empty($city)) && (preg_match('/[a-z\.\s]/i', $city)) &&
                (strlen($city) <= 30)) {
                //Sanitize the trimmed city
                $citytrim = $city;
        }else{
                $errors[] =
                'Missing city. Only alphabetic, period and space. Max 30.';
        }
        //Is the state or country present? If it is, sanitize it
        $state_country =
                filter_var( $_POST['state_country'], FILTER_SANITIZE_STRING);
        if ((!empty($state_country)) &&
                (preg_match('/[a-z\.\s]/i', $state_country)) &&
                        (strlen($state_country) <= 30)) {
                //Sanitize the trimmed state or country
                $state_countrytrim = $state_country;
        }else{
                $errors[] =
        'Missing state/country. Only alphabetic, period and space. Max 30.';}
        //Is the zip code or post code present? If it is, sanitize it
        $zcode_pcode =
                filter_var( $_POST['zcode_pcode'], FILTER_SANITIZE_STRING);
        $string_length = strlen($zcode_pcode);
        if ((!empty($zcode_pcode)) &&
                (preg_match('/[a-z0-9\s]/i', $zcode_pcode))  &&
                        ($string_length <= 30) && ($string_length >= 5)) {
        //Sanitize the trimmed zcode_pcode
                $zcode_pcodetrim = $zcode_pcode;
        }else{
                $errors[] =
'Missing zip code or post code. Alpha, numeric, space only max 30 characters';
        }
        
        //Is the phone number present? If it is, sanitize it
        $phone = filter_var( $_POST['phone'], FILTER_SANITIZE_STRING);
        if ((!empty($phone)) && (strlen($phone) <= 30)) {
                //Sanitize the trimmed phone number
                $phonetrim = (filter_var($phone, FILTER_SANITIZE_NUMBER_INT));
                $phonetrim = preg_replace('/[^0-9]/', ", $phonetrim");
        }else{
                $phonetrim = NULL;
        }
        
        if (empty($errors)) { // If everything's OK.
                // If no problems encountered, register user in the database
                //Determine whether the email address has already been registered
                $query = "SELECT userid FROM users WHERE email = ? ";
                $q = mysqli_stmt_init($dbcon);
                mysqli_stmt_prepare($q, $query);
                mysqli_stmt_bind_param($q,'s', $emailtrim);
                mysqli_stmt_execute($q);
                $result = mysqli_stmt_get_result($q);
        if (mysqli_num_rows($result) == 0){
        //The email address has not been registered
        //already therefore register the user in the users table
        //-------------Valid Entries - Save to database -----
        //Start of the SUCCESSFUL SECTION.
        // i.e., all the required fields were filled out
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                // Register the user in the database...
                $query = "INSERT INTO users (userid, title, first_name, ";
                $query .= "last_name, email, password, class, ";
                $query .= "address1, address2, city, state_country, ";
                $query .= "zcode_pcode, phone, secret, registration_date) ";
                $query .= "VALUES ";
                $query .= "(' ',?,?,?,?,?,?,?,?,?,?,?,?,?,NOW())";
                $q = mysqli_stmt_init($dbcon);
                mysqli_stmt_prepare($q, $query);
// use prepared statement to ensure that only text is inserted
// bind fields to SQL Statement
                mysqli_stmt_bind_param($q, 'sssssssssssss',
                        $titletrim, $first_nametrim, $last_nametrim, $emailtrim,
                        $hashed_password, $classtrim, $address1trim,
                        $address2trim, $citytrim, $state_countrytrim,
                        $zcode_pcodetrim, $phonetrim, $secrettrim);
                // execute query
                mysqli_stmt_execute($q);
                if (mysqli_stmt_affected_rows($q) == 1) {
                header ("location: register-thanks.php?class=" . $classtrim);
        } else {
        // echo 'Invalid query:' . $dbcon->error;
                $errorstring = "System is busy, please try later";
                echo "<p class=' text-center col-sm-2'
                        style='color:red'>$errorstring</p>";
        }
        }else{//The email address is already registered
                $errorstring = 'The email address is already registered.';
                echo "<p class=' text-center col-sm-2'
                        style='color:red'>$errorstring</p>";
        }
} else {//End of SUCCESSFUL SECTION
        // ---------------Process User Errors---------------
        // Display the users entry errors
        $errorstring = 'Error! The following error(s) occurred: ';
        foreach ($errors as $msg) { // Print each error.
                $errorstring .= " - $msg<br>\n";
        }
        $errorstring .= 'Please try again.';
        echo "<p class=' text-center col-sm-2' style=
                'color:red'>$errorstring</p>";
}// End of if (empty($errors)) IF.
}
catch(Exception $e)
{
        print "The system is busy, please try later";
        //print "An Exception occurred. Message: " . $e->getMessage();
}
catch(Error $e)
{
        print "The system is busy, please come back later";
        //print "An Error occurred. Message: " . $e->getMessage();
}
?>
