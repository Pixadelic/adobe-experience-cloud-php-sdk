<?php
/**
 * Created by PhpStorm.
 * User: pixadelic
 * Date: 06/04/2018
 * Time: 16:14
 */

$appRoot = __DIR__.'/..';
ini_set('error_log', $appRoot.'/var/log/php_error.log');
require $appRoot.'/vendor/autoload.php';
require $appRoot.'/web/utils.php';

use Pixadelic\Adobe\Api\AccessToken;
use Pixadelic\Adobe\Client\CampaignStandard;
use Symfony\Component\Yaml\Yaml;

$data = [];
$testEmail = null;
$testServiceName = null;
$newProfileTestEmail = null;
$campaignClient = null;

/**
 * Load and prepare config
 */
$config = Yaml::parseFile($appRoot.'/app/config/config.yml');
if (isset($config['adobe']['campaign']['private_key'])) {
    $config['adobe']['campaign']['private_key'] = $appRoot.'/'.$config['adobe']['campaign']['private_key'];
}
if (isset($config['parameters']['test_email'])) {
    $testEmail = $config['parameters']['test_email'];
}
if (isset($config['parameters']['new_profile_test_email'])) {
    $newProfileTestEmail = $config['parameters']['new_profile_test_email'];
}
if (isset($config['parameters']['test_service_name'])) {
    $testServiceName = $config['parameters']['test_service_name'];
}

/**
 * Getting access token
 */
$accessToken = new AccessToken($config['adobe']['campaign']);
$accessToken->flush();

execute($accessToken, 'get');

/**
 * CampaignStandard client example
 */
$campaignClient = new CampaignStandard($config['adobe']['campaign']);
$campaignClient->flush();
$prefix = get_class($campaignClient).'->';

execute($campaignClient, 'getProfileMetadata', []);
// getResource down!
//execute($campaignClient, 'getResource', ['postalAddress']);
execute($campaignClient, 'getProfiles');
execute($campaignClient, 'getNext', [$data[$prefix.'getProfiles']['success']]);
execute($campaignClient, 'getProfiles', [10, 'email']);
execute($campaignClient, 'getNext', [$data[$prefix.'getProfiles_alt']['success']]);
execute($campaignClient, 'getProfilesExtended');
execute($campaignClient, 'getNext', [$data[$prefix.'getProfilesExtended']['success']]);
execute($campaignClient, 'getProfilesExtended', [10, 'email']);
execute($campaignClient, 'getNext', [$data[$prefix.'getProfilesExtended_alt']['success']]);
execute($campaignClient, 'getProfileByEmail', [end($data[$prefix.'getNext_alt_alt_alt']['success']->content)]);
execute($campaignClient, 'getProfileByEmail', [$testEmail]);
$testProfile = $data[$prefix.'getProfileByEmail_alt']['success']->content[0];
execute($campaignClient, 'updateProfile', [$testProfile->PKey, ['preferredLanguage' => 'fr_fr']]);
execute($campaignClient, 'updateProfile', [$testProfile->PKey, ['foo' => 'bar']]);
execute($campaignClient, 'getServices');
$testService = null;
foreach ($data[$prefix.'getServices']['success']->content as $service) {
    if ($service->name === $testServiceName) {
        $testService = $service;
        break;
    }
}
execute($campaignClient, 'addSubscription', [$testProfile, $testService]);
execute($campaignClient, 'addSubscription', [$testProfile, $testService]);
execute($campaignClient, 'getSubscriptionsByProfile', [$testProfile]);
$testSubscription = null;
foreach ($data[$prefix.'getSubscriptionsByProfile']['success']->content as $subscription) {
    if ($subscription->serviceName === $testServiceName) {
        $testSubscription = $subscription;
        break;
    }
}
execute($campaignClient, 'deleteSubscription', [$testSubscription]);
execute($campaignClient, 'getProfileExtended', [$testProfile->PKey]);
execute($campaignClient, 'createProfile', [['foo' => 'bar']]);
execute($campaignClient, 'createProfile', [['email' => 'foo@bar']]);
execute($campaignClient, 'createProfile', [['email' => 'foo@wwwwwwwwwww.xyz']]);
execute($campaignClient, 'createProfile', [['email' => $testEmail]]);
execute($campaignClient, 'createProfile', [['email' => $newProfileTestEmail]]);
$newProfileTest = $campaignClient->getProfileByEmail($newProfileTestEmail);
execute($campaignClient, 'addSubscription', [$newProfileTest->content[0], $testService]);
execute($campaignClient, 'getSubscriptionsByProfile', [$newProfileTest->content[0]]);
$newProfileSubscription = null;
foreach ($data[$prefix.'getSubscriptionsByProfile_alt']['success']->content as $subscription) {
    if ($subscription->serviceName === $testServiceName) {
        $newProfileSubscription = $subscription;
        break;
    }
}
execute($campaignClient, 'deleteSubscription', [$newProfileSubscription]);
execute($campaignClient, 'deleteProfile', [$newProfileTest->content[0]->PKey]);

?><!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Adobe Experience Cloud PHP SDK test run</title>
    <style>
        body {
            font-family: sans-serif;
            line-height: 1.5;
            /*background-color: #fafafa;*/
            /*color: #222;*/
            background-color: #3A3636;
            color: #d1d1d1;
        }

        h1 {
            margin: 0;
            padding: 1rem 1.5rem;
            font-size: 24px;
            line-height: 1.2;
        }

        pre {
            margin: 0;
        }

        .toggler {
            cursor: pointer;
        }

        .togglable {
            display: none;
            box-sizing: border-box;
            padding: 0 1.5rem 1rem;
        }

        .togglable-opened {
            display: block;
        }

        .success {
            margin-bottom: 1px;
        }

        .check {
            color: greenyellow;
        }

        .ballot {
            color: gold;
        }

        .error {
            padding: 1rem 1.5rem;
            margin-bottom: 1px;
            background: #ff2600;
            color: #fff;
        }

        .error strong {
            color: #222;
            font-size: 9rem;
            float: left;
            display: block;
            padding: 0 1.5rem 0 0;
            margin-bottom: -1rem;
            line-height: 1;
        }

        .error h1 {
            padding: 0 0 .5rem 0;
        }

        .error div {
            padding: 0 0 1rem 0;
            float: left;
            max-width: 70%;
            overflow: auto;
        }

        .error pre {
            clear: both;
            max-width: 100%;
            overflow: auto;
            color: #efefef;
        }

    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/9.12.0/styles/rainbow.min.css">
</head>
<body>
<header><h1>Adobe Experience Cloud PHP SDK test run</h1></header>
<?php foreach ($data as $key => $value) : ?>
    <?php if (isset($data[$key]['success'])) : ?>
        <section class="success">
            <h1 class="toggler"><span class="check">✔</span> <?php print $key ?></h1>
            <div class="togglable">
                <pre><code><?php print var_export($data[$key]['success'], true); ?></code></pre>
            </div>
        </section>
    <?php endif; ?>
    <?php if (isset($data[$key]['error'])) : ?>
        <section class="error">
            <strong><?php print $data[$key]['error']->getCode(); ?></strong>
            <div><h1><span class="ballot">✘</span> <?php print $key ?></h1>
                <?php print $data[$key]['error']->getMessage(); ?></div>
            <pre><?php print $data[$key]['error']->getTraceAsString(); ?></pre>
        </section>
    <?php endif; ?>
<?php endforeach; ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/randomcolor/0.5.2/randomColor.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/9.12.0/highlight.min.js"></script>
<script>
    (function (document, randomColor) {
        "use strict";
        let togglables = document.querySelectorAll(".togglable"),
            i          = 0;

        function init(togglable, i) {
            let toggler     = togglable.parentElement.querySelector(".toggler"),
                togglerHtml = toggler.innerHTML,
                color       = randomColor({
                    luminosity: "dark",
                    format: "hsla",
                    hue: "green"
                });

            toggler.style.backgroundColor   = color;
            togglable.style.backgroundColor = color;
            toggler.innerHTML += " +";

            toggler.addEventListener("click", function () {
                if (togglable.classList.contains("togglable-opened")) {
                    togglable.classList.remove("togglable-opened");
                    toggler.innerHTML = togglerHtml + " +";
                } else {
                    togglable.classList.add("togglable-opened");
                    toggler.innerHTML = togglerHtml + " -";
                }
            }, false);
        }

        if (togglables.length) {
            for (i = 0; i < togglables.length; i += 1) {
                init(togglables[i], i);
            }
        }

        hljs.initHighlightingOnLoad();

    }(document, randomColor));
</script>
</body>
</html>