<?php

	require_once("includes/ActiveCampaign.class.php");

	$ac = new ActiveCampaign("https://bemtevi39488.api-us1.com", "fa737462df9d6ecb4f54c2f0ba3a6fb65ee07c4eb12170c857ce30b4cf785dcfe65091e9");

	/*
	 * TEST API CREDENTIALS.
	 */

	if (!(int)$ac->credentials_test()) {
		echo "<p>Access denied: Invalid credentials (URL and/or API key).</p>";
		exit();
	}
	
        echo "<p>Credentials valid! Proceeding...</p>";
	
	/*
	 * VIEW ACCOUNT DETAILS.
	 */

	$account = $ac->api("account/view");

	echo "<pre>";
	print_r($account);
	echo "</pre>";

	/*
	 * ADD NEW LIST.
	 */

	$list = array(
		"name"           => "List 3",
		"sender_name"    => "My Company",
		"sender_addr1"   => "123 S. Street",
		"sender_city"    => "Chicago",
		"sender_zip"     => "60601",
		"sender_country" => "USA",
	);

	$list_add = $ac->api("list/add", $list);

	if (!(int)$list_add->success) {
		// request failed
		echo "<p>Adding list failed. Error returned: " . $list_add->error . "</p>";
		exit();
	}
        
        // successful request
        $list_id = (int)$list_add->id;
        echo "<p>List added successfully (ID {$list_id})!</p>";

	/*
	 * ADD OR EDIT CONTACT (TO THE NEW LIST CREATED ABOVE).
	 */

	$contact = array(
		"email"              => "test@example.com",
		"first_name"         => "Test",
		"last_name"          => "Test",
		"p[{$list_id}]"      => $list_id,
		"status[{$list_id}]" => 1, // "Active" status
	);

	$contact_sync = $ac->api("contact/sync", $contact);

	if (!(int)$contact_sync->success) {
		// request failed
		echo "<p>Syncing contact failed. Error returned: " . $contact_sync->error . "</p>";
		exit();
	}
        
        // successful request
        $contact_id = (int)$contact_sync->subscriber_id;
        echo "<p>Contact synced successfully (ID {$contact_id})!</p>";

	/*
	 * VIEW ALL CONTACTS IN A LIST (RETURNS ID AND EMAIL).
	 */

	$ac->version(2);
	$contacts_view = $ac->api("contact/list?listid=14&limit=500");

	$ac->version(1);

	/*
	 * ADD NEW EMAIL MESSAGE (FOR A CAMPAIGN).
	 */

	$message = array(
		"format"        => "mime",
		"subject"       => "Check out our latest deals!",
		"fromemail"     => "newsletter@test.com",
		"fromname"      => "Test from API",
		"html"          => "<p>My email newsletter.</p>",
		"p[{$list_id}]" => $list_id,
	);

	$message_add = $ac->api("message/add", $message);

	if (!(int)$message_add->success) {
		// request failed
		echo "<p>Adding email message failed. Error returned: " . $message_add->error . "</p>";
		exit();
	}
        
        // successful request
        $message_id = (int)$message_add->id;
        echo "<p>Message added successfully (ID {$message_id})!</p>";

	/*
	 * CREATE NEW CAMPAIGN (USING THE EMAIL MESSAGE CREATED ABOVE).
	 */

	$campaign = array(
		"type"             => "single",
		"name"             => "July Campaign", // internal name (message subject above is what contacts see)
		"sdate"            => "2013-07-01 00:00:00",
		"status"           => 1,
		"public"           => 1,
		"tracklinks"       => "all",
		"trackreads"       => 1,
		"htmlunsub"        => 1,
		"p[{$list_id}]"    => $list_id,
		"m[{$message_id}]" => 100, // 100 percent of subscribers
	);

	$campaign_create = $ac->api("campaign/create", $campaign);

	if (!(int)$campaign_create->success) {
		// request failed
		echo "<p>Creating campaign failed. Error returned: " . $campaign_create->error . "</p>";
		exit();
	}
        
        // successful request
        $campaign_id = (int)$campaign_create->id;
        echo "<p>Campaign created and sent! (ID {$campaign_id})!</p>";

	/*
	 * VIEW CAMPAIGN REPORTS (FOR THE CAMPAIGN CREATED ABOVE).
	 */

	$campaign_report_totals = $ac->api("campaign/report/totals?campaignid={$campaign_id}");

	echo "<p>Reports:</p>";
	echo "<pre>";
	print_r($campaign_report_totals);
	echo "</pre>";

?>

<p><b>Note</b>: It can also be helpful to check our <a href="http://www.activecampaign.com/api/overview.php">API documentation</a> for the HTTP method that should be used for a particular endpoint as it can affect the format of your request.</p>
<p>Example: <pre>list_field_view</pre> GET</p>
<pre>
$ac->api("list/field/view?ids=all");
<pre>
<p>Query params appended for a GET request.</p>

<p>Example: <pre>list_field_edit</pre> POST</p>
<pre>
$ac->api("list/field/edit", array(/*POST params here.*/));
</pre>

<a href="http://www.activecampaign.com/api">View more API examples!</a>
