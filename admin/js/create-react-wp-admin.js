;(function ($) {
  'use strict'
  $(document).ready(function () {
    /*********
     * Start *
     *********/

    const AJAXURL = crwp.ajaxurl
    const APPS = crwp.apps

    // console.log({ AJAXURL, APPS })
    // TODO onchange form input
    $('#crwp-create-form').validate()

    // processing event on button click
    $(document).on('submit', '#crwp-create-form', () => {
      console.log('submit')
      const appname = $('#crwp-appname')
      const fieldset = $('#crwp-create-fieldset')
      const pageslug = $('#crwp-pageslug')
      const spinner = $('#crwp-spinner')
      const postdata = `action=crwp_admin_ajax_request&param=create_react_app&appname=${appname.val()}&pageslug=${pageslug.val()}`

      fieldset.prop('disabled', true)
      spinner.addClass('is-active')
      $.post(AJAXURL, postdata, (response) => {
        const result = JSON.parse(response)
        if (result.status) {
          console.log({ appname })
          appname.val('')
          fieldset.prop('disabled', false)
          pageslug.val('')
          spinner.removeClass('is-active')
        }
        console.log({ result })
      })
    })

    $('.button-start').click(() => {
      console.log('.button-start')
      showSnackbar('React app started.')
    })

    function showSnackbar(message = '') {
      $('#crwp-snackbar').addClass('show').text(message)

      setTimeout(() => $('#crwp-snackbar').removeClass('show').text(''), 5000)
    }

    /*******
     * END *
     *******/
  })
})(jQuery)
