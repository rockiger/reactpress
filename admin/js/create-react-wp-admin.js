;(function ($) {
  'use strict'
  $(document).ready(function () {
    /*********
     * Start *
     *********/

    const AJAXURL = crwp.ajaxurl
    const APPS = crwp.apps

    // console.log({ AJAXURL, APPS })
    // TODO1.0.1 onchange form input
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

    $('.button-start-stop').click((ev) => {
      console.log('.button-start')
      console.log($(ev.target).data())
      const buttonNode = $(ev.target)
      const buttonState = buttonNode.text()
      const { appname = null, pageslug = null } = buttonNode.data()
      const postdata = `action=crwp_admin_ajax_request&param=${
        buttonState === 'Stop' ? 'stop' : 'start'
      }_react_app&appname=${appname}&pageslug=${pageslug}`
      const spinnerNode = $(`#crwp-spinner-${appname}`)

      buttonNode.prop('disabled', true)
      spinnerNode.addClass('is-active')
      $.post(AJAXURL, postdata, (response) => {
        const result = JSON.parse(response)
        const statusNode = $(`#status-${appname}`)
        if (result.status && buttonState === 'Start') {
          const { ip, port, protocol } = result
          statusNode.removeClass('fg-red')
          statusNode.addClass('fg-green')
          statusNode.html(
            `Running at port: <a class="button-link" href="${protocol}://${ip}:${port}" rel="noopener" target="_blank">${port}<i class="external-link"></i></a>`
          )
          buttonNode.text('Stop')
        } else if (result.status && buttonState === 'Stop') {
          statusNode.removeClass('fg-green')
          statusNode.addClass('fg-red')
          statusNode.html(`Stopped`)
          buttonNode.text('Start')
        }
        buttonNode.prop('disabled', false)
        spinnerNode.removeClass('is-active')
        showSnackbar(result.message)
        console.log({ result })
        console.log(response)
      })
    })

    // TODO Check if servers are running every 10 seconds

    function showSnackbar(message = '') {
      $('#crwp-snackbar').addClass('show').text(message)
      setTimeout(() => $('#crwp-snackbar').removeClass('show').text(''), 5000)
    }

    /*******
     * END *
     *******/
  })
})(jQuery)
