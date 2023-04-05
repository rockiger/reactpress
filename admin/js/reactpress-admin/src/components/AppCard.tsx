import _ from 'lodash'
import AddPageInput from './AddPageForm'
import PageLink from './PageLink'

import cra from './cra.svg'
import empty from './empty.svg'
import orphan from './orphan.svg'
import vite from './vite.svg'

export interface Page {
  ID: number
  permalink: string
  title: string
}
export interface AppDetails {
  allowsRouting: boolean
  appname: string
  pageIds: number[]
  pages: Page[]
  type?:
    | 'deployment_cra'
    | 'deployment_vite'
    | 'development_cra'
    | 'development_vite'
    | 'empty'
    | 'orphan'
}

export interface AppCardProps {
  addPage: (appname: string, pageId: number, pageTitle: string) => void
  app: AppDetails
  appspath: string
  deleteApp: (appname: string) => void
  deletePage: (appname: string, page: Page) => void
  deletingApps: string[]
  getPages: (search?: string, pageIds?: number[]) => void
  pages: Page[]
  toggleRouting: (appname: string) => void
  updatingApps: string[]
  updateDevEnvironment: (appname: string, permalink: string) => void
}

const APP_TYPES = {
  deployment_cra: 'CRA Production',
  deployment_vite: 'Vite Production',
  development_cra: 'CRA Development',
  development_vite: 'Vite Development',
  empty: 'Empty Folder - It seems no build folder was added.',
  orphan: 'Orphan - It seems the app folder was deleted.',
}

const icons = {
  deployment_cra: cra,
  deployment_vite: vite,
  development_cra: cra,
  development_vite: vite,
  orphan: orphan,
  empty: empty,
}

const iconDescriptions = {
  deployment_cra: 'Create-React-App Logo',
  deployment_vite: 'Vite Logo',
  development_cra: 'Create-React-App Logo',
  development_vite: 'Vite Logo',
  orphan: 'Empty Document Icon',
  empty: 'Empty Folder Icon',
}

function AppCard({
  addPage,
  app,
  appspath,
  deleteApp,
  deletePage,
  deletingApps,
  getPages,
  pages,
  toggleRouting,
  updateDevEnvironment,
  updatingApps,
}: AppCardProps) {
  return (
    <div id={app.appname} className="AppCard card col flex fullwidth m0 p2">
      <h3 className="title flex m0 mb-05 row">
        <img
          alt={iconDescriptions[app?.type ?? 'orphan']}
          className="icon"
          src={icons[app.type ?? 'orphan']}
        />
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
            <th scope="row">Target Pages</th>
            <td>
              {_.map(app.pages, (page) => (
                <PageLink
                  app={app}
                  key={page.ID}
                  page={page}
                  deletePage={deletePage}
                />
              ))}
              <AddPageInput
                addPage={addPage}
                app={app}
                isDisabled={!_.isEmpty(app.pageIds) && app.allowsRouting}
                getPages={getPages}
                pages={pages}
              />
              {!_.isEmpty(app.pageIds) && app.allowsRouting && (
                <span className="fg-orange">
                  Apps with client-side routing can only have URL slug.
                </span>
              )}
              {_.isEmpty(app.pageIds) && (
                <>
                  <p className="fg-red">
                    <b>Please choose a WordPress page for your app!</b>
                  </p>
                  <p className="description">
                    Set the target page slug for your React app.
                  </p>
                </>
              )}
            </td>
          </tr>
          <tr>
            <th scope="row">Type</th>
            <td>
              <span style={{ textTransform: 'capitalize' }}>
                {APP_TYPES[app?.type || 'development_cra']}
              </span>
            </td>
          </tr>
          {app.type?.startsWith('development') && (
            <>
              <tr>
                <th scope="row">Update Dev-Environtment</th>
                <td>
                  <button
                    className="button button-update"
                    disabled={
                      _.isEmpty(app.pageIds) ||
                      _.includes(updatingApps, app.appname)
                    }
                    onClick={() =>
                      updateDevEnvironment(
                        app.appname,
                        _.get(_.last(app.pages), 'permalink', '')
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

      <details className="py125">
        <summary>
          <h4 className="inline-block">Advanced Features</h4>
        </summary>

        <table className="form-table" role="presentation">
          <tbody>
            <tr>
              <th scope="row">Routing</th>
              <td>
                <fieldset>
                  <label htmlFor="allow_routing">
                    <input
                      checked={app.allowsRouting}
                      disabled={app.pageIds.length !== 1}
                      id="allow_routing"
                      name="allow_routing"
                      onChange={() => toggleRouting(app.appname)}
                      type="checkbox"
                    />
                    <span
                      className={
                        app.pageIds.length !== 1 ? 'disabled fg-grey' : ''
                      }
                    >
                      Use clean URLs
                    </span>{' '}
                    {app.pageIds.length !== 1 && (
                      <span className="fg-orange">
                        Clean URLs can only be activated for apps with one
                        single page slug.
                      </span>
                    )}
                  </label>
                </fieldset>

                <p className="description">
                  Check if you want to use a routing library like React Router
                  with clean URLs. That means your React pages can't have sub
                  pages and only one slug will work properly.{' '}
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
          </tbody>
        </table>
      </details>
      <div>
        <button
          className="button-link button-delete fg-red-dark"
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
