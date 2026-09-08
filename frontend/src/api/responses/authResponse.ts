export type UserResponse = {
  id: number
  name: string
  email: string
}

export type LoginResponse = {
  token: string
  user: UserResponse
}
