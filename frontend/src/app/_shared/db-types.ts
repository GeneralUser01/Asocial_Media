export interface WithId {
  id: number,
}

export interface CreatedAtTimestamp {
  created_at: string,
}
export interface UpdatedAtTimestamp {
  created_at: string,
}
export type Timestamps = CreatedAtTimestamp & UpdatedAtTimestamp;

/** By default Laravel wraps its data inside another object that contains meta
 * data.
 *
 * For more info see:
 * https://laravel.com/docs/8.x/eloquent-resources#data-wrapping
 */
export interface Wrapped<T> {
  /** The main data returned from the backend. */
  data: T,
}

/** By default Laravel wraps its data inside another object that contains meta
 * data.
 *
 * For more info see:
 * https://laravel.com/docs/8.x/eloquent-resources#data-wrapping-and-pagination
 */
export interface WrappedCollection<T> {
  /** The main data returned from the backend. */
  data: T,
  /** Useful links for this collection. */
  links: CollectionLinks,
  /** Information about this collection. */
  meta: CollectionMetadata,
}

export interface CollectionMetadata {
  /** One based index of the first entry included in this response. Can be
   * `null` if there isn't any entries for this page. */
  from: number | null,
  /** One based index of the last entry included in this response. Can be `null`
   * if there isn't any entries for this page. */
  to: number | null,

  /** Number of entries per page. */
  per_page: number,
  /** Total number of entries in this collection. */
  total: number,

  /** One based index of the page that this response if for. */
  current_page: number,
  /** One based index of the last page. */
  last_page: number,

  /** URL of the resource without any page query appended to it.  */
  path: string,
  /** Useful links like previous, next and specific page numbers. */
  links: CollectionMetadataLink[],
}

export interface CollectionMetadataLink {
  /** URL for this link. Can be `null` if its a previous or next link and there
   * isn't anymore pages in this direction. */
  url: string | null,
  /** Suggested label for this link. */
  label: string,
  /** `true` if this link leads back to the currently selected page. */
  active: boolean,
}

export interface CollectionLinks {
  /** URL for the first page in this collection. */
  first: string,
  /** URL for the last page in this collection. */
  last: string,

  /** URL for the previous page in this collection. */
  prev: string | null,
  /** URL for the next page in this collection. */
  next: string | null,
}

export type Opinion = 'liked' | 'disliked' | 'neutral';

export interface Likeable {
  /** Total number of likes. */
  likes?: number,
  /** Total number of dislikes. */
  dislikes?: number,
  /** Your opinion. */
  opinion?: Opinion,
}