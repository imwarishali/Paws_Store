<?php

/**
 * Google Analytics Helper
 * Insert this in your page header
 */

function getGoogleAnalyticsCode($trackingId = 'GA_TRACKING_ID')
{
    if ($trackingId === 'GA_TRACKING_ID') {
        return '<!-- Add your Google Analytics tracking ID to db.php or config.php -->';
    }

    return <<<HTML
<!-- Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id={$trackingId}"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());
  gtag('config', '{$trackingId}');
</script>
HTML;
}

// Track custom event
function trackEvent($category, $action, $label = '', $value = null)
{
    echo "<script>
        if (typeof gtag !== 'undefined') {
            gtag('event', '" . addslashes($action) . "', {
                'event_category': '" . addslashes($category) . "',
                'event_label': '" . addslashes($label) . "'" . ($value ? ", 'value': " . intval($value) : "") . "
            });
        }
    </script>";
}
