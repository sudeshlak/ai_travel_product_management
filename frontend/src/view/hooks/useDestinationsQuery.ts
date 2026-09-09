import { useQuery } from '@tanstack/react-query'
import * as destinationService from '@/service/destinationService'

export const destinationsQueryKey = ['destinations'] as const

export function useDestinationsQuery() {
  return useQuery({
    queryKey: destinationsQueryKey,
    queryFn: () => destinationService.listDestinations(),
  })
}
