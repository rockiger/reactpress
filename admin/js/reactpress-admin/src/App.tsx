import React, { useCallback, useEffect, useState } from 'react'
import _ from 'lodash'
import logo from './logo.svg'
import './App.css'
import AppCard, { AppDetails } from './components/AppCard'

interface RP {
  ajaxurl: string
  apps: AppDetails[]
  appspath: string
}
declare var rp: RP
declare var jQuery: any

function App() {
  console.log(rp)
  const [apps, setApps] = useState<RP['apps']>(rp.apps)
  const [deletingApps, setDeletingApps] = useState<string[]>([])
  const [toggledSlugButtons, setToggledSlugButtons] = useState<string[]>([])
  const [editingAppSlugs, setEditingAppSlugs] = useState<string[]>([])

  const deleteApp = useCallback(async (appname: string) => {
    const is_delete = window.confirm(
      `Do you really want to delete app ${appname}? This will delete all files and cant't be undone!`
    )
    if (is_delete) {
      setDeletingApps((deletingApps) => _.concat(deletingApps, appname))
      //call to api
      const response = await jQuery
        .post(
          rp.ajaxurl,
          `action=repr_admin_ajax_request&param=delete_react_app&appname=${appname}`
        )
        .then()
      const result = JSON.parse(response)
      if (result.status) {
        // remove app from apps
        setApps((apps) => _.filter(apps, (app) => app.appname !== appname))
        showSnackbar(result.message)
      } else {
        showSnackbar("Couldn't delete app.")
      }
      setDeletingApps((deletingApps) => _.without(deletingApps, appname))
    }
  }, [])

  const getApps = async () => {
    const response = await jQuery
      .post(rp.ajaxurl, `action=repr_admin_ajax_request&param=get_react_apps`)
      .then()
    const result = JSON.parse(response)
    if (result.apps) {
      setApps(result.apps)
    }
  }

  const toggleSlugButton = useCallback(
    (appname: string) => {
      if (_.includes(toggledSlugButtons, appname)) {
        setToggledSlugButtons((toggledSlugButtons) =>
          _.without(toggledSlugButtons, appname)
        )
      } else {
        setToggledSlugButtons((toggledSlugButtons) =>
          _.concat(toggledSlugButtons, appname)
        )
      }
    },
    [toggledSlugButtons]
  )

  const editSlug = useCallback(
    async (appname: string, newSlug: string) => {
      toggleSlugButton(appname)
      setEditingAppSlugs((editingApps) => _.concat(editingApps, appname))
      //call to api
      const response = await jQuery
        .post(
          rp.ajaxurl,
          `action=repr_admin_ajax_request&param=edit_url_slug&appname=${appname}&pageslug=${newSlug}`
        )
        .then()
      const result = JSON.parse(response)
      if (result.status) {
        setApps((apps) =>
          _.map(apps, (app) =>
            app.appname === appname ? { ...app, pageslug: newSlug } : app
          )
        )
        showSnackbar(result.message)
      } else {
        showSnackbar("Couldn't change page slug.")
      }
      setEditingAppSlugs((editingApps) => _.without(editingApps, appname))
    },
    [toggleSlugButton]
  )

  useEffect(() => {
    getApps()
  }, [setApps])

  return (
    <div className="App rp-content">
      <header className="head">
        <div className="head--inner align-center flex m0auto maxWidth80 p2 pb1 pt1">
          <img className="logo" src={logo} alt="logo" />
          <h1 style={{ color: '#82878C' }}>ReactPress</h1>
        </div>
      </header>
      <div className="maxWidth80 m0auto p2">
        <h2 className="mb075">React Apps</h2>
        {_.isEmpty(apps) ? (
          <div className="flex gap1 row">
            <div className="col flex grow1 half">
              <p className="pb1">
                It seems you don't have any React apps created. Go to{' '}
                <code>{rp.appspath}</code> in your command line and enter:
              </p>
              <p className="pb1">
                <code>npx create-react-app [appname]</code>
              </p>
              <p className="pb1">
                Insert a page slug and start developing your app with{' '}
                <code>yarn start</code>.
              </p>
              <p className="pb1">
                To deploy your React app, install ReactPress on your live
                system, build the app with <code>yarn build</code> and upload
                only the build folder to{' '}
                <code>wp-content/reactpress/[appname]</code> on your live
                system.
              </p>
              <p className="pb1">
                Then reload the ReactPress page in the WordpPress admin and give
                it the exact same slug as on the dev system.
              </p>
              <p className="pb1">
                If you visit the slug now, you should see the app on your live
                system.
              </p>
            </div>
          </div>
        ) : (
          <div className="flex gap2 row">
            <div className="col flex grow1 twoThirds">
              <div id="existing-apps" className="flex flexwrap gap1 row">
                {_.map(apps, (app) => (
                  <AppCard
                    app={app}
                    appspath={rp.appspath}
                    deleteApp={deleteApp}
                    deletingApps={deletingApps}
                    editingAppSlugs={editingAppSlugs}
                    editSlug={editSlug}
                    toggledSlugButtons={toggledSlugButtons}
                    toggleSlugButton={toggleSlugButton}
                  />
                ))}
              </div>
            </div>

            <div className="col flex grow1 oneThird">
              <p className="pt1">
                You can find <b>all app sources</b> in your WordPress plugin
                folder under:
                <code>{`${rp.appspath}/[appname]`.replace('//', '/')}</code>.
              </p>
              <p className="pt1">
                <b>For deployments</b> to work, make sure, that you{' '}
                <b>upload the build folder</b> of your React app into the app
                directory and that you have the <b>same folder structure</b> in
                your dev and live wordpress installation.
              </p>
            </div>
          </div>
        )}
      </div>
    </div>
  )
}

export default App

function showSnackbar(message = '') {
  jQuery('#rp-snackbar').addClass('show').text(message)
  setTimeout(() => jQuery('#rp-snackbar').removeClass('show').text(''), 5000)
}
