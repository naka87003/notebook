export type User = {
  id: number;
  name: string;
  email: string;
  email_verified_at?: string;
  image_path?: string;
  comment?: string;
  created_at: string;
  updated_at: string;
};

export type Note = {
  id: number;
  title?: string;
  starts_at?: string;
  ends_at?: string;
  content?: string;
  public: number;
  image_path?: string;
  category_id: number;
  tag_id?: number;
  status_id: number;
  status?: Status;
  tag?: Tag;
  category?: Category;
  user?: User;
  likes?: Like[];
  likes_count: number;
  comments_count: number;
  created_at: string;
  updated_at: string;
};

export type Sort = {
  key: string;
  order: string;
};

export type NotesFilter = {
  category: number[];
  tag: number[];
  status: number;
  onlyLiked: boolean;
};

export type PostsFilter = {
  onlyMyLiked: boolean;
  user: number;
  following: boolean;
};

export type Category = {
  id: number;
  name: string;
  vuetify_theme_color_name: string;
  mdi_name: string;
};

export type Tag = {
  id: number;
  name: string;
  hex_color?: string;
  user_id?: number;
};

export type TagCount = {
  normal_count: number;
  archived_count: number;
};

export type Status = {
  id: number;
  name: string;
};

export type Like = {
  id: number;
  user_id: number;
  user?: User;
  updated_at: string;
};

export type Notification = {
  id: string;
  notifiable_id: number;
  notifiable_type: string;
  data: {
    type: 'follow' | 'comment' | 'like' | 'reply';
    comment?: Comment;
    note_id?: number;
  };
  read_at: string;
  created_at: string;
  updated_at: string;
  user: User;
};

export type Comment = {
  id: number;
  content: string;
  user_id: number;
  note_id: number;
  created_at: string;
  updated_at: string;
  user?: User;
  replies_count?: number;
};

export type Reply = {
  id: number;
  content: string;
  user_id: number;
  comment_id: number;
  reply_to?: number;
  created_at: string;
  updated_at: string;
  user?: User;
  addressee?: User;
};

export type EmailPrerefence = {
  id: number;
  user_id: number;
  type: string;
  value: number;
  created_at: string;
  updated_at: string;
};
