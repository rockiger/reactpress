<div class="card fullwidth p2">
  <h2>Advanced Settings</h2>
  <form id="rp-create-form" method="post" action="javascript:void(0)">
    <fieldset id="rp-create-fieldset">
      <input hidden name="action" value="CREATE_NEW_APP" />
      <table class="form-table" role="presentation">
        <tbody>
          <tr>
            <th scope="row">Path to app directory</th>
            <td>
              <input id="rp-appname" name="app_name" placeholder="e.g. my-email-app" required type="text" />
              <p class="description">You can set the plugin directory manually, because in some server configurations we can't determine it programmatically.</p>
            </td>
          </tr>
        </tbody>
      </table>
      <div>
        <input type="submit" name="submit" id="submit" class="button button-primary" value="Save Settings" />
        <span id="rp-spinner" class="crpw-button-spinner spinner"></span>
      </div>
    </fieldset>
  </form>
</div>