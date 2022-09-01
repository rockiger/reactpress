import _ from 'lodash'
import { useRef, useState } from 'react'
import type { AppCardProps } from './AppCard'

interface UrlSlugFormProps {
  appname: string
  deleteSlug?: AppCardProps['deleteSlug']
  isDisabled?: boolean
  pageslug: string
  updateSlug: AppCardProps['updateSlug']
}

export default UrlSlugForm
export function UrlSlugForm({
  appname,
  deleteSlug,
  isDisabled = false,
  pageslug,
  updateSlug,
}: UrlSlugFormProps) {
  const inputRef = useRef<HTMLInputElement>(null)
  const [showInput, setShowInput] = useState<Boolean>(false)
  const [showSpinner, setShowSpinner] = useState<Boolean>(false)
  console.log(deleteSlug, !_.isEmpty(pageslug))
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
              defaultValue={pageslug}
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
            {!_.isEmpty(pageslug) && (
              <a className="inline-block lh1 pt05" href={pageslug}>
                {pageslug}
              </a>
            )}
            <button
              className="button button-icon button-link-to-slug"
              //@ts-ignore
              disabled={showSpinner || isDisabled}
              onClick={() => setShowInput(true)}
            >
              {_.isEmpty(pageslug) ? (
                'Add slug'
              ) : (
                <span className="dashicons dashicons-edit"></span>
              )}
            </button>
          </>
        )}
        {deleteSlug && !_.isEmpty(pageslug) && (
          <button
            className="bd-none bg-none fg-red hover:fg-red pointer"
            onClick={() => deleteSlug(appname, pageslug)}
          >
            <span className="dashicons dashicons-trash"></span>
          </button>
        )}
      </div>
    </>
  )

  async function submitNewSlug() {
    setShowInput(false)
    if (_.get(inputRef, 'current.value') !== pageslug) {
      setShowSpinner(true)
      await updateSlug(appname, _.get(inputRef, 'current.value', ''), pageslug)
      setShowSpinner(false)
    }
  }
}
