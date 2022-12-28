//@ts-nocheck
import _ from 'lodash'
import { useRef, useState } from 'react'
import type { AppCardProps } from './AppCard'

interface UrlSlugFormProps {
  appname: string
  deletePage?: AppCardProps['deleteSlug']
  isDisabled?: boolean
  pageId: number
  permalink: string
  updateSlug: AppCardProps['updateSlug']
}

export default UrlSlugForm
export function UrlSlugForm({
  appname,
  deletePage,
  isDisabled = false,
  pageId,
  updateSlug,
}: UrlSlugFormProps) {
  const inputRef = useRef<HTMLInputElement>(null)
  const [showInput, setShowInput] = useState<Boolean>(false)
  const [showSpinner, setShowSpinner] = useState<Boolean>(false)
  console.log(deletePage, !_.isEmpty(pageId))
  return (
    <>
      <span
        className={`crpw-button-spinner spinner ${
          showSpinner ? 'is-active' : ''
        }`}
      ></span>
      {showSpinner && (
        <p className="fg-orange float-right">
          <b>This may take several minutes.</b>
        </p>
      )}
      <div className="flex gap05 mb025">
        {showInput ? (
          <>
            <input
              autoFocus
              defaultValue={pageId}
              onKeyUp={async (ev) => {
                if (ev.key === 'Enter') {
                  ev.preventDefault()
                  await submitNewSlug()
                }
              }}
              ref={inputRef}
              type="text"
            />
            <button
              className="button button-primary button-edit-slug-save"
              onClick={async () => submitNewSlug()}
            >
              Save
            </button>
            <button
              className="button button-link button-edit-slug-cancel ml025"
              onClick={() => setShowInput(false)}
            >
              Cancel
            </button>
          </>
        ) : (
          <>
            {!_.isEmpty(pageId) && (
              <a className="inline-block lh1 pt05" href={pageId}>
                {pageId}
              </a>
            )}
            <button
              className="button button-icon button-link-to-slug"
              //@ts-ignore
              disabled={showSpinner || isDisabled}
              onClick={() => setShowInput(true)}
            >
              {_.isEmpty(pageId) ? (
                'Add slug'
              ) : (
                <span className="dashicons dashicons-edit"></span>
              )}
            </button>
          </>
        )}
        {deletePage && !_.isEmpty(pageId) && (
          <button
            className="bd-none bg-none fg-red hover:fg-red pointer"
            onClick={() => deletePage(appname, pageId, permalink)}
          >
            <span className="dashicons dashicons-trash"></span>
          </button>
        )}
      </div>
    </>
  )

  async function submitNewSlug() {
    setShowInput(false)
    if (_.get(inputRef, 'current.value') !== pageId) {
      setShowSpinner(true)
      await updateSlug(appname, _.get(inputRef, 'current.value', ''), pageId)
      setShowSpinner(false)
    }
  }
}
