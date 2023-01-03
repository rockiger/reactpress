import { useEffect, useMemo, useState } from 'react'
import type { ActionMeta, InputActionMeta, SingleValue } from 'react-select'
import CreatableSelect from 'react-select/creatable'
import type { AppCardProps, Page } from './AppCard'

interface CreatableSelectOption {
  label: string
  value: number
  __isNew__?: boolean
}
interface AddPageInputProps {
  addPage: AppCardProps['addPage']
  app: AppCardProps['app']
  isDisabled: boolean
  getPages: AppCardProps['getPages']
  pages: Page[]
}

export default AddPageInput
export function AddPageInput({
  addPage,
  app,
  isDisabled = false,
  getPages,
  pages,
}: AddPageInputProps) {
  const [inputValue, setInputValue] = useState<string>('')
  const [selectValue, setSelectValue] = useState<CreatableSelectOption | null>(
    null
  )
  const [showInput, setShowInput] = useState<Boolean>(false)
  const [showSpinner, setShowSpinner] = useState<Boolean>(false)

  const pageOptions = useMemo(
    () => pages.map((p) => ({ label: p.title, value: p.ID })),
    [pages]
  )
  useEffect(() => {
    getPages(inputValue, app.pageIds)
  }, [app.pageIds, getPages, inputValue])

  const onChange = (
    newValue: SingleValue<{
      label: string
      value: number
    }>,
    actionMeta: ActionMeta<{
      label: string
      value: number
    }>
  ) => {
    if (
      actionMeta.action === 'select-option' ||
      actionMeta.action === 'create-option'
    ) {
      setInputValue(newValue?.label || '')
      setSelectValue(newValue)
    } else if (actionMeta.action === 'clear') {
      setInputValue('')
      setSelectValue(null)
    }
  }
  const onInputChange = (newValue: string, actionMeta: InputActionMeta) => {
    // Only react to user input for change
    if (actionMeta.action === 'input-change') {
      setSelectValue(null)
      setInputValue(newValue)
    }
  }

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
            <CreatableSelect
              autoFocus
              isClearable
              onChange={onChange}
              onInputChange={onInputChange}
              openMenuOnFocus
              options={pageOptions}
              placeholder="Select Page..."
              styles={{
                container: (baseStyles, state) => ({
                  ...baseStyles,
                  flexBasis: 230,
                  flexShrink: 0.5,
                  margin: '0 1px',
                }),
                control: (baseStyles, state) => ({
                  ...baseStyles,
                  borderColor: state.isFocused ? '#2271b1' : '#8c8f94',
                  boxShadow: state.isFocused ? '0 0 0 1px #2271b1' : '',
                  minHeight: 30,
                  //outline: 2px solid transparent;
                  '&:hover': {
                    borderColor: '#2271b1',
                    boxShadow: '0 0 0 1px #2271b1',
                  },
                  '&:focus': {
                    borderColor: '#2271b1',
                    boxShadow: '0 0 0 1px #2271b1',
                  },
                }),
                indicatorsContainer: (baseStyles, state) => ({
                  ...baseStyles,
                  '> div': {
                    color: '#8c8f94',
                    padding: '0 8px',
                  },
                  '> span': {
                    backgroundColor: '#8c8f94',
                  },
                }),
                input: (baseStyles, state) => ({
                  ...baseStyles,
                  input: { boxShadow: 'none !important', minHeight: 0 },
                }),
                option: (baseStyles, state) => ({
                  ...baseStyles,
                  backgroundColor: state.isSelected ? '#2271b1' : undefined,
                  '&:hover': {
                    backgroundColor: '#2271b1',
                    color: 'white',
                  },
                }),
                valueContainer: (baseStyles, state) => ({
                  ...baseStyles,
                  padding: '1px 8px',
                }),
              }}
            />
            <button
              className="button button-primary button-edit-slug-save"
              disabled={!selectValue}
              onClick={() => submitNewPage()}
            >
              {selectValue?.__isNew__ ? 'Create Page' : 'Add Page'}
            </button>
            <button
              className="button button-edit-slug-cancel ml025"
              onClick={() => {
                setShowInput(false)
                setInputValue('')
              }}
            >
              Cancel
            </button>
          </>
        ) : (
          <button
            className="button button-link-to-slug"
            //@ts-ignore
            disabled={isDisabled || showSpinner}
            onClick={() => setShowInput(true)}
          >
            Add Page
          </button>
        )}
      </div>
    </>
  )

  async function submitNewPage() {
    setShowInput(false)
    setInputValue('')
    if (selectValue) {
      setShowSpinner(true)
      if (selectValue?.__isNew__) {
        await addPage(app.appname, -1, selectValue.label)
      } else {
        await addPage(app.appname, selectValue.value, selectValue.label)
      }
      setShowSpinner(false)
    }
  }
}
