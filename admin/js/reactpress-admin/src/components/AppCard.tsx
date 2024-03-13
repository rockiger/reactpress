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
      <table className="form-table" role="presentation">
        <tbody>
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
              {_.isEmpty(app.pageIds) && (
                <>
                  <p className="fg-red">
                    <b>
                      Please choose a WordPress page where you want to display
                      your Fulcrum wiki!
                    </b>
                  </p>
                </>
              )}
              <p className="description">
                You can choose as many pages you want to display your wiki.
              </p>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  )
}

export default AppCard
