import _ from 'lodash'
import React from 'react'
import icon from './icon.svg'
import UrlSlugForm from './UrlSlugForm'

export interface AppDetails {
  allowsRouting: boolean
  appname: string
  pageslugs: string[]
  type?: 'development' | 'deployment' | 'empty' | 'orphan'
}

export interface AppCardProps {
  app: AppDetails
  appspath: string
  deleteApp: (appname: string) => void
  deleteSlug: (appname: string, pageslug: string) => void
  deletingApps: string[]
  toggleRouting: (appname: string) => void
  updateSlug: (appname: string, newSlug: string, oldSlug: string) => void
  updatingApps: string[]
  updateDevEnvironment: (appname: string, pageslug: string) => void
}

const APP_TYPES = {
  deployment: 'Production',
  development: 'Development',
  empty: 'Empty Folder - It seems no build folder was added.',
  orphan: 'Orphan - It seems the app folder was deleted.',
}

function AppCard({
  app,
  appspath,
  deleteApp,
  deleteSlug,
  deletingApps,
  toggleRouting,
  updateSlug,
  updateDevEnvironment,
  updatingApps,
}: AppCardProps) {
  return (
    <div id={app.appname} className="AppCard card col flex fullwidth m0 p2">
      <h3 className="title flex m0 mb-05 row">
        <img alt="" className="icon" src={icon} />
        {app.appname}
      </h3>

      <table className="form-table" role="presentation">
        <tbody>
          <tr>
            <th scope="row">App Directory</th>
            <td>
              <code className="line-break">
                {`${appspath}/${app.appname}`.replace('//', '/')}
              </code>
            </td>
          </tr>
          <tr>
            <th scope="row">URL Slug</th>
            <td>
              {_.map(app.pageslugs, (pageslug) => (
                <UrlSlugForm
                  appname={app.appname}
                  deleteSlug={deleteSlug}
                  key={pageslug}
                  updateSlug={updateSlug}
                  pageslug={pageslug}
                />
              ))}
              <UrlSlugForm
                appname={app.appname}
                isDisabled={!_.isEmpty(app.pageslugs) && app.allowsRouting}
                pageslug={''}
                updateSlug={updateSlug}
              />{' '}
              {!_.isEmpty(app.pageslugs) && app.allowsRouting && (
                <span className="fg-orange">
                  Apps with client-side routing can only have URL slug.
                </span>
              )}
              {_.isEmpty(app.pageslugs) && (
                <>
                  <p className="fg-red">
                    <b>Please choose a URL slug for your app!</b>
                  </p>
                  <p className="description">
                    Set the page slug for your React app. The URL slug must not
                    be used by another page.
                  </p>
                </>
              )}
            </td>
          </tr>
          <tr>
            <th scope="row">Routing</th>
            <td>
              <fieldset>
                <label htmlFor="allow_routing">
                  <input
                    checked={app.allowsRouting}
                    disabled={app.pageslugs.length !== 1}
                    id="allow_routing"
                    name="allow_routing"
                    onChange={() => toggleRouting(app.appname)}
                    type="checkbox"
                  />
                  <span
                    className={
                      app.pageslugs.length !== 1 ? 'disabled fg-grey' : ''
                    }
                  >
                    Use clean URLs
                  </span>{' '}
                  {app.pageslugs.length !== 1 && (
                    <span className="fg-orange">
                      Clean URLs can only be activated for apps with one single
                      page slug.
                    </span>
                  )}
                </label>
              </fieldset>

              <p className="description">
                Check if you want to use a routing library like React Router
                with clean URLs. That means your React pages can't have sub
                pages and only one slug will work properly.
                <a
                  href="https://rockiger.com/en/reactpress/client-side-routing/"
                  rel="noreferrer"
                  target="_blank"
                >
                  Learn more about client-side routing
                </a>{' '}
                in ReactPress.
              </p>
            </td>
          </tr>
          <tr>
            <th scope="row">Type</th>
            <td>
              <span style={{ textTransform: 'capitalize' }}>
                {APP_TYPES[app?.type || 'development']}
              </span>
            </td>
          </tr>
          {app.type === 'development' && (
            <>
              <tr>
                <th scope="row">Update Dev-Environtment</th>
                <td>
                  <button
                    className="button button-update"
                    disabled={
                      _.isEmpty(app.pageslugs) ||
                      _.includes(updatingApps, app.appname)
                    }
                    onClick={() =>
                      updateDevEnvironment(
                        app.appname,
                        _.first(app.pageslugs) || ''
                      )
                    }
                  >
                    Update Dev-Environment
                  </button>
                  <span
                    className={`crpw-button-spinner spinner ${
                      _.includes(updatingApps, app.appname) ? 'is-active' : ''
                    }`}
                  ></span>
                  {_.includes(updatingApps, app.appname) && (
                    <p className="fg-orange float-right">
                      <b>This may take several minutes.</b>
                    </p>
                  )}
                  <p className="description">
                    Update the <code>index.html</code> of your local react dev
                    environment, to match the styles of your WordPress
                    installation.
                  </p>
                </td>
              </tr>
              <tr>
                <th scope="row">Manual Build</th>
                <td>
                  <p className="description">
                    Build the app in your command line with{' '}
                    <code>npm run build</code> or <code>yarn build</code>.
                  </p>
                </td>
              </tr>
            </>
          )}
        </tbody>
      </table>
      <div>
        <button
          className="button-link button-delete"
          onClick={() => deleteApp(app.appname)}
        >
          Delete App
        </button>
        <span
          className={`crpw-button-spinner spinner ${
            _.includes(deletingApps, app.appname) ? 'is-active' : ''
          }`}
        ></span>
      </div>
    </div>
  )
}

export default AppCard
