import _ from 'lodash'
import React from 'react'
import icon from './icon.svg'

export interface AppDetails {
  appname: string
  pageslug: string
  type?: 'development' | 'deployment' | 'empty' | 'orphan'
}

export interface AppCardProps {
  app: AppDetails
  appspath: string
  deleteApp: (appname: string) => void
  deletingApps: string[]
}

const TYPES = {
  deployment: 'Production',
  development: 'Development',
  empty: 'Empty Folder - It seems no build folder was added.',
  orphan: 'Orphan - It seems the app folder was deleted.',
}

function AppCard({ app, appspath, deleteApp, deletingApps }: AppCardProps) {
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
              <div id={`link-to-slug-${app.appname}`}>
                {!app.pageslug ? (
                  <i className="fg-grey inline-block lh1 pt05">Not set.</i>
                ) : (
                  <a className="inline-block lh1 pt05" href={app.pageslug}>
                    {app.pageslug}
                  </a>
                )}
                <button
                  className="button button-icon button-link-to-slug"
                  data-appname={app.appname}
                >
                  <span
                    className="dashicons dashicons-edit"
                    data-appname={app.appname}
                  ></span>
                </button>
              </div>
              <div id={`edit-slug-${app.appname}`} style={{ display: 'none' }}>
                <input
                  id={`edit-slug-input-${app.appname}`}
                  type="text"
                  value={app.pageslug}
                />
                <button
                  className="button button-primary button-edit-slug-save"
                  id={`edit-slug-save-${app.appname}`}
                  data-appname={app.appname}
                  data-pageslug={app.pageslug}
                >
                  Save
                </button>
                <button
                  className="button button-link button-edit-slug-cancel ml025"
                  id={`edit-slug-cancel-${app.appname}`}
                  data-appname={`app.appname`}
                >
                  Cancel
                </button>
              </div>
              {!app.pageslug && (
                <p className="fg-red">
                  <b>Please choose a URL slug for your app!</b>
                </p>
              )}
              <p className="description">
                Set the page slug for your React app. The URL slug must not be
                used by another page.
              </p>
            </td>
          </tr>
          <tr>
            <th scope="row">Type</th>
            <td>
              <span style={{ textTransform: 'capitalize' }}>
                {' '}
                {TYPES[app?.type || 'development']}
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
                    data-appname={app.appname}
                    data-pageslug={app.pageslug}
                    disabled={!app.pageslug}
                  >
                    Update Dev-Environment
                  </button>
                  <span
                    id={`rp-start-spinner-${app.appname}`}
                    className="crpw-button-spinner spinner"
                  ></span>
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
