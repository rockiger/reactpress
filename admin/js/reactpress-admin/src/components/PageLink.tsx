import type { Page, AppCardProps, AppDetails } from './AppCard'

export default function PageLink({
  app,
  deletePage,
  page,
}: {
  app: AppDetails
  deletePage: AppCardProps['deletePage']
  page: Page
}) {
  return (
    <div
      className="align-baseline flex gap05 pb05 title column-title has-row-actions column-primary page-title show-children"
      data-colname="Title"
    >
      <strong>
        <a
          className="row-title"
          href={`/wp-admin/post.php?post=${page.ID}&amp;action=edit`}
          aria-label={`“${page.title}” (Edit)`}
        >
          {page.title}
        </a>
      </strong>
      <div className="fg-grey-light font-size-sm hide p0">
        <span className="edit">
          <a
            href={`/wp-admin/post.php?post=${page.ID}&amp;action=edit`}
            aria-label={`Edit “${page.title}”`}
          >
            Edit
          </a>{' '}
          |{' '}
        </span>
        <span className="trash">
          <button
            className="button-link button-delete fg-red-dark"
            onClick={() => deletePage(app.appname, page)}
          >
            Remove
          </button>{' '}
          |{' '}
        </span>
        <span className="view">
          <a
            href={page.permalink}
            rel="bookmark"
            aria-label={`View “${page.title}”`}
          >
            View
          </a>
        </span>
      </div>
    </div>
  )
}
