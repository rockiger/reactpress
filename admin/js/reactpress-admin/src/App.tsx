import { useCallback, useEffect, useMemo, useState } from 'react'
import _ from 'lodash'
import WPAPI from 'wpapi'
import './App.css'
import AppCard from './components/AppCard'
import type { AppDetails, Page } from './components/AppCard'

export interface RP {
  ajaxurl: string
  api: {
    nonce: string
    rest_url: string
  }
  apps: AppDetails[]
  appspath: string
  pluginDirUrl: string
}
declare var rp: RP
declare var jQuery: any

const wp = new WPAPI(
  isDevEnvironment()
    ? {
        endpoint: rp.api.rest_url,
        username: 'admin',
        password: 'Chrf A6HY 2B9t YAsx qIpi foOM',
      }
    : { endpoint: rp.api.rest_url, nonce: rp.api.nonce }
)

const logo = `${rp.pluginDirUrl}admin/js/reactpress-admin/build/logo.svg`

function App() {
  const [apps, setApps] = useState<RP['apps']>(rp.apps)
  const [deletingApps, setDeletingApps] = useState<string[]>([])
  const [pages, setPages] = useState<Page[]>([])
  const [updatingApps, setUpdatingApps] = useState<string[]>([])

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

  const getApps = useCallback(async () => {
    if (isDevEnvironment()) {
      return console.log('getApps fired')
    }
    try {
      const response = await jQuery
        .post(rp.ajaxurl, `action=repr_admin_ajax_request&param=get_react_apps`)
        .then()
      const result = JSON.parse(response)
      if (result.apps) {
        setApps(result.apps)
      }
    } catch (e) {
      console.error(e)
    }
  }, [setApps])

  // We use useMemo here, because typescript can't handle _.debounce properly
  const getPages = useMemo(
    () =>
      _.debounce(async (search = '', pageIds: number[] = []) => {
        try {
          const response = await wp
            .pages()
            .status(['any'])
            .param('exclude', pageIds)
            .get()
          setPages(
            response.map(
              (el: {
                id: string
                link: string
                title: { rendered: string }
              }) => ({
                ID: el.id,
                permalink: el.link,
                title: el.title.rendered,
              })
            )
          )
        } catch (e) {
          console.error(e)
        }
      }),
    []
  )

  const addPage = useCallback(
    async (appname: string, pageId: number, pageTitle: string) => {
      if (isDevEnvironment()) {
        console.log({ appname, pageId, pageTitle })
        return
      }
      try {
        //call to api
        const response = await jQuery
          .post(
            rp.ajaxurl,
            `action=repr_admin_ajax_request&param=add_page&appname=${appname}&pageId=${pageId}&page_title=${pageTitle}`
          )
          .then()
        const result = JSON.parse(response)
        if (result.status) {
          setApps((apps) =>
            _.map(apps, (app) => {
              if (app.appname !== appname) return app
              return {
                ...app,
                pageIds: [...app.pageIds, result.pageId],
                pages: [
                  ...app.pages,
                  {
                    ID: result.pageId,
                    permalink: result.permalink,
                    title: result.page_title,
                  },
                ],
              }
            })
          )
        }
        showSnackbar(result.message)
      } catch (e) {
        console.error(e)
        showSnackbar("Couldn't add page.")
      }
    },
    []
  )

  const deletePage = useCallback(async (appname: string, page: Page) => {
    const changeState = (add = false) =>
      setApps((apps) =>
        _.map(apps, (app) => {
          if (app.appname !== appname) return app
          return {
            ...app,
            pageIds: add
              ? _.concat(app.pageIds, page.ID)
              : _.without(app.pageIds, page.ID),
            pages: add ? _.concat(app.pages, page) : _.without(app.pages, page),
          }
        })
      )

    //optimistically update state
    changeState()

    if (isDevEnvironment()) return
    try {
      //call to api
      const response = await jQuery
        .post(
          rp.ajaxurl,
          `action=repr_admin_ajax_request&param=delete_page&appname=${appname}&pageId=${page.ID}`
        )
        .then()
      const result = JSON.parse(response)
      if (!result.status) {
        changeState(true)
        showSnackbar(result.message)
      }
    } catch (e) {
      console.log(e)
      changeState(true)
      showSnackbar("Couldn't remove page.")
    }
  }, [])

  const toggleRouting = useCallback(async (appname: string) => {
    const changeState = () =>
      setApps((apps) =>
        _.map(apps, (app) => {
          if (app.appname !== appname) return app
          return { ...app, allowsRouting: !app.allowsRouting }
        })
      )

    //optimistically update state
    changeState()

    if (isDevEnvironment()) return
    try {
      //call to api
      const response = await jQuery
        .post(
          rp.ajaxurl,
          `action=repr_admin_ajax_request&param=toggle_react_routing&appname=${appname}`
        )
        .then()
      const result = JSON.parse(response)
      if (!result.status) {
        changeState()
        showSnackbar(result.message)
      }
    } catch (e) {
      console.log(e)
      changeState()
      showSnackbar("Couldn't save app routing")
    }
  }, [])

  const updateDevEnvironment = useCallback(
    async (appname: string, permalink: string) => {
      setUpdatingApps((updatingApps) => _.concat(updatingApps, appname))
      //call to api
      const response = await jQuery
        .post(
          rp.ajaxurl,
          `action=repr_admin_ajax_request&param=update_index_html&appname=${appname}&permalink=${permalink}`
        )
        .then()
      const result = JSON.parse(response)
      if (result.status) {
        showSnackbar(result.message)
      } else {
        showSnackbar("Couldn't update dev environment.")
      }
      setUpdatingApps((updatingApps) => _.without(updatingApps, appname))
    },
    []
  )

  /**
   * Make sure apps are updated often enough.
   */
  useEffect(() => {
    getApps()
    window.addEventListener('focus', getApps)
    const timerId = setInterval(() => getApps(), 5 * 60 * 1000)

    return () => {
      window.removeEventListener('focus', getApps)
      clearInterval(timerId)
    }
  }, [getApps])

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
                Add a page and start developing your app with{' '}
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
                Then reload the ReactPress page in the WordpPress admin and add
                a page again.
              </p>
              <p className="pb1">
                If you visit the assigned page now, you should see the app on
                your live system.
              </p>
            </div>
          </div>
        ) : (
          <div className="flex gap2 row">
            <div className="col flex grow1 twoThirds">
              <div id="existing-apps" className="flex flexwrap gap1 row">
                {_.map(apps, (app) => (
                  <AppCard
                    addPage={addPage}
                    app={app}
                    appspath={rp.appspath}
                    deleteApp={deleteApp}
                    deletePage={deletePage}
                    deletingApps={deletingApps}
                    getPages={getPages}
                    pages={pages}
                    key={app.appname}
                    toggleRouting={toggleRouting}
                    updateDevEnvironment={updateDevEnvironment}
                    updatingApps={updatingApps}
                  />
                ))}
              </div>
            </div>

            <div className="col flex grow1 oneThird">
              <p className="pt1">
                You can find <b>all app sources</b> in your WordPress plugin
                folder under:
                <code className="line-break">
                  {`${rp.appspath}/[appname]`.replace('//', '/')}
                </code>
                .
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

function isDevEnvironment() {
  return process.env.NODE_ENV === 'development'
}

function showSnackbar(message = '') {
  jQuery('#rp-snackbar').addClass('show').text(message)
  setTimeout(() => jQuery('#rp-snackbar').removeClass('show').text(''), 5000)
}
