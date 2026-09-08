import type { LoginResponse } from '@/api/responses/authResponse'
import type { User } from '@/types/User'

export type MappedLogin = {
  token: string
  user: User
}

export function mapLoginResponse(response: LoginResponse): MappedLogin {
  return {
    token: response.token,
    user: {
      id: response.user.id,
      name: response.user.name,
      email: response.user.email,
    },
  }
}
