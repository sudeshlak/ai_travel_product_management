export class UnauthorizedError extends Error {
  constructor() {
    super('UnauthorizedError')
  }
}

export class ValidationError extends Error {
  readonly fields: Record<string, string>

  constructor(fields: Record<string, string>) {
    super('ValidationError')
    this.fields = fields
  }
}

export class UnexpectedError extends Error {
  constructor() {
    super('UnexpectedError')
  }
}

export class ConnectionError extends Error {
  constructor() {
    super('ConnectionError')
  }
}
