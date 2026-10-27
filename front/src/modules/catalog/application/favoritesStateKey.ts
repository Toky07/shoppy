import type { InjectionKey } from 'vue'
import type { FavoritesState } from './createFavoritesState'

export const favoritesStateKey: InjectionKey<FavoritesState> = Symbol('favoritesState')
