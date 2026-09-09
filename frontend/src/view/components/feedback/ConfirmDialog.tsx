type ConfirmDialogProps = {
  open: boolean
  title: string
  message: string
  confirmLabel?: string
  cancelLabel?: string
  busy?: boolean
  onConfirm: () => void
  onCancel: () => void
}

function ConfirmDialog({
  open,
  title,
  message,
  confirmLabel = 'Confirm',
  cancelLabel = 'Cancel',
  busy = false,
  onConfirm,
  onCancel,
}: ConfirmDialogProps) {
  if (!open) {
    return null
  }

  return (
    <>
      <div
        className="modal fade show d-block"
        tabIndex={-1}
        role="dialog"
        aria-modal="true"
        aria-labelledby="confirm-dialog-title"
      >
        <div className="modal-dialog modal-dialog-centered">
          <div className="modal-content">
            <div className="modal-header">
              <h2 id="confirm-dialog-title" className="modal-title h5 mb-0">
                {title}
              </h2>
              <button
                type="button"
                className="btn-close"
                aria-label="Close"
                disabled={busy}
                onClick={onCancel}
              />
            </div>
            <div className="modal-body">
              <p className="mb-0">{message}</p>
            </div>
            <div className="modal-footer">
              <button
                type="button"
                className="btn btn-outline-secondary"
                disabled={busy}
                onClick={onCancel}
              >
                {cancelLabel}
              </button>
              <button
                type="button"
                className="btn btn-danger"
                disabled={busy}
                onClick={onConfirm}
              >
                {busy ? 'Working…' : confirmLabel}
              </button>
            </div>
          </div>
        </div>
      </div>
      <div
        className="modal-backdrop fade show"
        onClick={busy ? undefined : onCancel}
      />
    </>
  )
}

export default ConfirmDialog
