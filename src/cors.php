<?php
    try {
        switch ($_SESSION["security-level"]) {
            default:
            case "0": 
                $lEnableJavaScriptValidation = false;
                $lEnableHTMLControls = false;
                break;

            case "1": 
                $lEnableJavaScriptValidation = true;
                $lEnableHTMLControls = true;
                break;

            case "2":
            case "3":
            case "4":
            case "5": 
                $lEnableHTMLControls = true;
                $lEnableJavaScriptValidation = true;
                break;
        }
    } catch (Exception $e) {
        echo $CustomErrorHandler->FormatError($e, "Error setting up configuration on page cors.php");
    }
?>

<div class="page-title">Cross-origin Resource Sharing (CORS)</div>

<?php include_once __SITE_ROOT__.'/includes/back-button.inc'; ?>
<?php include_once __SITE_ROOT__.'/includes/hints/hints-menu-wrapper.inc'; ?>

<script type="text/javascript">
    var onSubmitOfForm = function(theForm) {
        var lText = theForm.idMessageInput.value;

        <?php if ($lEnableJavaScriptValidation) { ?>
            var lOSCommandInjectionPattern = /[;&|<>]/;
            var lCrossSiteScriptingPattern = /[<>=()]/;
        <?php } else { ?>
            var lOSCommandInjectionPattern = /[]/;
            var lCrossSiteScriptingPattern = /[]/;
        <?php } ?>

        if (lText.search(lOSCommandInjectionPattern) > -1) {
            alert("Malicious characters are not allowed.");
            return false;
        } else if (lText.search(lCrossSiteScriptingPattern) > -1) {
            alert("Characters used in cross-site scripting are not allowed.");
            return false;
        }

        var lXMLHTTP = new XMLHttpRequest();
        var lURL = "http://cors.{$_SERVER['SERVER_NAME']}/webservices/rest/cors-server.php";
        var lAsynchronously = true;
        var lMessage = encodeURIComponent(lText);
        var lMethod = encodeURIComponent(theForm.idMethod.value);
        var lSendACAOHeader = theForm.idACAO.checked ? "True" : "False";
        var lSendACAMHeader = theForm.idACAM.checked ? "True" : "False";
        var lSendACMAHeader = theForm.idACMA.checked ? "True" : "False";
        var lMaxAge = encodeURIComponent(theForm.idMaxAgeInput.value || 600); // Default to 600

        var lQueryParameters = 
            "message=" + lMessage + 
            "&method=" + lMethod + 
            "&acao=" + lSendACAOHeader + 
            "&acam=" + lSendACAMHeader + 
            "&acma=" + lSendACMAHeader + 
            "&max-age=" + lMaxAge;

        lXMLHTTP.onreadystatechange = function() {
            if (this.readyState == 4) {
                document.getElementById("idMessageOutput").innerHTML = lXMLHTTP.responseText;
            }
        };

        switch (theForm.idMethod.value) {
            case "GET":
                lXMLHTTP.open(lMethod, lURL + "?" + lQueryParameters, lAsynchronously);
                lXMLHTTP.send();
                break;
            case "POST":
            case "PUT":
            case "PATCH":
            case "DELETE":
                lXMLHTTP.open(lMethod, lURL, lAsynchronously);
                lXMLHTTP.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
                lXMLHTTP.send(lQueryParameters);
                break;
        }
    };
</script>

<form>
    <table>
        <tr>
            <td colspan="2" class="form-header">Enter message to echo</td>
        </tr>
        <tr>
            <td class="label">Message</td>
            <td>
                <input 
                    type="text" 
                    id="idMessageInput" 
                    name="message" 
                    size="20"
                    autofocus="autofocus"
                    <?php if ($lEnableHTMLControls) echo 'minlength="1" maxlength="20" required="required"'; ?>
                />
            </td>
        </tr>
        <tr>
            <td class="label">HTTP Method</td>
            <td>
                <input type="radio" id="idMethod" name="method" value="GET" checked /> GET<br>
                <input type="radio" id="idMethod" name="method" value="POST" /> POST<br>
                <input type="radio" id="idMethod" name="method" value="PUT" /> PUT<br>
                <input type="radio" id="idMethod" name="method" value="PATCH" /> PATCH<br>
                <input type="radio" id="idMethod" name="method" value="DELETE" /> DELETE<br>
            </td>
        </tr>
        <tr>
            <td class="label">Response Headers to Send</td>
            <td>
                <input type="checkbox" id="idACAO" name="acao" checked /> Access-Control-Allow-Origin<br>
                <input type="checkbox" id="idACAM" name="acam" checked /> Access-Control-Allow-Methods<br>
                <input type="checkbox" id="idACMA" name="acma" checked /> Access-Control-Max-Age<br>
            </td>
        </tr>
        <tr>
            <td class="label">Max-Age (in seconds)</td>
            <td>
                <input 
                    type="number" 
                    id="idMaxAgeInput" 
                    name="max-age" 
                    min="0" 
                    max="86400" 
                    value="600" 
                />
            </td>
        </tr>
        <tr>
            <td colspan="2" style="text-align:center;">
                <input 
                    onclick="onSubmitOfForm(this.form);" 
                    name="echo-php-submit-button" 
                    class="button" 
                    type="button" 
                    value="Echo Message" 
                />
            </td>
        </tr>
    </table>
</form>

<div id="idMessageOutput"></div>
