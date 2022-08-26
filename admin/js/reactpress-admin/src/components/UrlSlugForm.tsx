import _ from 'lodash'
import { useRef, useState } from 'react'
import type { AppCardProps } from './AppCard'

interface UrlSlugFormProps {
  appname: string
  editSlug: AppCardProps['editSlug']
  pageslug: string
}

export default UrlSlugForm
export function UrlSlugForm({ appname, editSlug, pageslug }: UrlSlugFormProps) {
  const inputRef = useRef<HTMLInputElement>(null)
  const [showInput, setShowInput] = useState<Boolean>(false)
  const [showSpinner, setShowSpinner] = useState<Boolean>(false)

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
      <div className="flex gap025 mb025">
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
            {_.isEmpty(pageslug) ? (
              <i className="fg-grey inline-block lh1 pt05">Not set.</i>
            ) : (
              <a className="inline-block lh1 pt05" href={pageslug}>
                {pageslug}
              </a>
            )}
            <button
              className="button button-icon button-link-to-slug"
              //@ts-ignore
              disabled={showSpinner}
              onClick={() => setShowInput(true)}
            >
              <span className="dashicons dashicons-edit"></span>
            </button>
          </>
        )}
      </div>
    </>
  )

  async function submitNewSlug() {
    setShowInput(false)
    if (_.get(inputRef, 'current.value') !== pageslug) {
      setShowSpinner(true)
      await editSlug(appname, _.get(inputRef, 'current.value', ''), pageslug)
      setShowSpinner(false)
    }
  }
}
