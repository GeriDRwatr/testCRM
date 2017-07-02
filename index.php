<?php

$url = "http://geritestcrm.890m.com//service/v3_1/rest.php";
//$url = "http://cm.com//service/v3_1/rest.php";
$username = "admin";
$password = "1122";

//function to make cURL request
function call($method, $parameters, $url)
{
    ob_start();
    $curl_request = curl_init();

    curl_setopt($curl_request, CURLOPT_URL, $url);
    curl_setopt($curl_request, CURLOPT_POST, 1);
    curl_setopt($curl_request, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
    curl_setopt($curl_request, CURLOPT_HEADER, 1);
    curl_setopt($curl_request, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($curl_request, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl_request, CURLOPT_FOLLOWLOCATION, 0);

    $jsonEncodedData = json_encode($parameters);

    $post = array(
        "method" => $method,
        "input_type" => "JSON",
        "response_type" => "JSON",
        "rest_data" => $jsonEncodedData
    );

    curl_setopt($curl_request, CURLOPT_POSTFIELDS, $post);
    $result = curl_exec($curl_request);
    curl_close($curl_request);

    $result = explode("\r\n\r\n", $result, 2);
    $response = json_decode($result[1]);
    ob_end_flush();

    return $response;
}

//login ------------------------------
$login_parameters = array(
    "user_auth" => array(
        "user_name" => $username,
        "password" => md5($password),
        "version" => "1"
    ),
    "application_name" => "RestTest",
    "name_value_list" => array(),
);

$login_result = call("login", $login_parameters, $url);

//get session id
$session_id = $login_result->id;

if(!isset($_POST['module'])) {
    $_POST['module'] = "Contacts";
}

function get_modules($module_name, $session_id, $url) {

    $get_entry_list_parameters = array(
        'session' => $session_id,
        'module_name' => $module_name,
        'query' => "",
        'order_by' => "",
        'offset' => "0",
        'select_fields' => array(),
        'Favorites' => false
    );

    $get_entry_list_result = call("get_entry_list", $get_entry_list_parameters, $url);
    return $get_entry_list_result;
}

function find_intersect_fields($array, $possible_fields)
{
    $fields = [];
    foreach ($array as $field) {
        if (key_exists($field->{"name"}, $possible_fields)) {
            $field->{'title'} = $possible_fields[$field->{"name"}];
            array_push($fields, $field);
        }
    }

    return $fields;
}

if(isset($_POST['module'])) {
    $module_data = get_modules($_POST['module'], $session_id, $url);

    $possible_fields = [];
    switch ($_POST['module']) {
        case 'Contacts':
            $possible_fields = [
                'name' => 'Name',
                'title' => 'Title',
                'email1' => 'Email',
                'phone_work' => 'Phone'];
            break;
        case 'Leads':
            $possible_fields = [
                'name' => 'Name',
                'status'  => 'Status',
                'account_name' => 'Account',
                'email1' => 'Email',
                'phone_work' => 'Phone',
                'created_by_name' => 'Created By'];
            break;
        case 'Accounts':
            $possible_fields = [
                'name' => 'Name',
                'primary_address_city' => 'City',
                'primary_address_country' => 'Country',
                'phone_work' => 'Phone',
                'created_by_name' => 'Created By',
                'email1' => 'Email',
                'date_entered' => 'Created Date'];
            break;
        case 'Tasks':
            $possible_fields = [
                'name' => 'Name',
                'contact_name' => 'Contact',
                'date_due' => 'Due Date',
                'time_due' => 'Due Time',
                'assigned_user_name' => 'Assigned User',
                'date_entered' => 'Created Date'];
            break;
        case 'Opportunities':
            $possible_fields = [
                'name' => 'Name',
                'account_name' => 'Account',
                'sales_stage' => 'Stage',
                'amount' => 'Amount',
                'date_closed' => 'Date Closed',
                'assigned_user_name' => 'Assigned User',
                'date_entered' => 'Created Date'];
            break;
        case 'Users':
            $possible_fields = [
                'full_name' => 'Name',
                'name' => 'Username',
                'title' => 'Title',
                'department' => 'Department',
                'email1' => 'Email',
                'phone_work' => 'Phone',
                'status' => 'Status'];
            break;
    }

    $data_for_view = [];
    foreach ($module_data->{"entry_list"} as $object) {
        array_push($data_for_view, find_intersect_fields($object->{"name_value_list"}, $possible_fields));
    }
}
?>


<html>
    <head>
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
    </head>
        <body>
            <div class="container">
                <div class="row">
                    <?php if(!empty($data_for_view)) { ?>
                    <table class="table table-hover">
                        <tr class="active">
                            <?php foreach ($data_for_view[0] as $entry) { ?>
                                <th> <?php echo $entry->{"title"}; ?> </th>
                            <?php } ?>
                        </tr>
                        <?php foreach ($data_for_view as $object) { ?>
                        <tr>
                            <?php foreach ($object as $entry) { ?>
                                <td> <?php echo $entry->{"value"}; ?> </td>
                            <?php } ?>
                        </tr>
                        <?php } ?>
                    </table>
                    <?php } ?>
                </div>
                <div class="row">
                    <form method="post">
                        <input class="btn btn-primary" type="submit" name="module" value="Contacts">
                        <input class="btn btn-primary" type="submit" name="module" value="Leads">
                        <input class="btn btn-primary" type="submit" name="module" value="Accounts">
                        <input class="btn btn-primary" type="submit" name="module" value="Tasks">
                        <input class="btn btn-primary" type="submit" name="module" value="Opportunities">
                        <input class="btn btn-primary" type="submit" name="module" value="Users">
                    </form>
                </div>
            </div>
        </body>
</html>

