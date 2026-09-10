export const DESCRIPTION_MAX_LENGTH = 2000
export const DESCRIPTION_AI_MIN_WORDS = 4

export function countWords(text: string): number {
  const trimmed = text.trim()
  if (!trimmed) {
    return 0
  }
  return trimmed.split(/\s+/).filter(Boolean).length
}

export function canUseDescriptionAi(text: string): boolean {
  return countWords(text) >= DESCRIPTION_AI_MIN_WORDS && text.length <= DESCRIPTION_MAX_LENGTH
}
