<?php

namespace App\Models;

use App\Models\PostsModel;

/**
 * CommentsModel - Comments Model using Posttype System
 * 
 * This model handles post comments using the posttype system (cf_comments)
 * All comments are stored in the posts_cf_comments table via posttype
 * 
 * Features:
 * - Uses PostsModel with posttype 'cf_comments'
 * - BelongsTo relationship for post
 * - BelongsTo relationship for user (author)
 * - Self-referencing for parent/child comments
 * - Eager Loading support to prevent N+1 queries
 * 
 * @package Application\Models
 */
class CommentsModel extends PostsModel
{
    /** @var string Posttype slug */
    protected $postType = 'cf_comments';

    /**
     * Constructor - Initialize with posttype
     */
    public function __construct()
    {
        parent::__construct($this->postType, 'all'); // 'all' = non-multilingual
    }

    /** @var array Fillable fields */
    protected $fillable = [
        'title',
        'slug',
        'status',
        'posttype',      // Posttype của bài được comment (blogs, plugins, themes, ...)
        'user_id',
        'rating',
        'like_count',
        'content',
        'post_id',
        'par_comment',
        'author_name',
        'author_email',
        'ip_address',
        'user_agent',
    ];
    
    // =========================================================================
    // RELATIONSHIPS
    // =========================================================================

    /**
     * Post relationship (BelongsTo)
     * 
     * Comment belongs to one post
     * Note: This is a polymorphic relationship based on posttype
     * 
     * @param string $postType Post type to get
     * @return \System\Database\ModelRelations\BelongsTo|null
     */
    public function post($postType = 'posts')
    {
        $postsTable = posttype_name($postType);

        if (empty($postsTable)) {
            return null;
        }

        return $this->belongsTo(
            'App\Models\PostsModel',    // Related table
            'post_id',      // Foreign key on this model
            'id'            // Owner key on posts table
        );
    }

    /**
     * User/Author relationship (BelongsTo)
     * 
     * Comment belongs to one user
     * 
     * @return \System\Database\ModelRelations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(
            'App\Models\UsersModel',     // Related table
            'user_id',   // Foreign key on this model
            'id'         // Owner key on users table
        );
    }

    /**
     * Parent comment relationship (BelongsTo)
     * 
     * Comment belongs to one parent comment
     * 
     * @return \System\Database\ModelRelations\BelongsTo
     */
    public function parent()
    {
        $commentsTable = posttype_name('cf_comments');
        return $this->belongsTo(
            $commentsTable,   // Related table (self) - posts_cf_comments
            'par_comment',    // Foreign key on this model
            'id'              // Owner key on parent comment
        );
    }

    /**
     * Child comments relationship (HasMany)
     * 
     * Comment has many child comments (replies)
     * 
     * @return \System\Database\ModelRelations\HasMany
     */
    public function replies()
    {
        $commentsTable = posttype_name('cf_comments');
        return $this->hasMany(
            'App\Models\CommentsModel',   // Related table (self) - posts_cf_comments
            'par_comment',    // Foreign key on related model
            'id'              // Local key on this model
        );
    }
    
    // =========================================================================
    // QUERY SCOPES
    // =========================================================================

    /**
     * Scope: Active comments
     * 
     * @param \System\Database\Query\Builder $query
     * @return \System\Database\Query\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope: By post posttype (post_posttype field)
     * 
     * @param \System\Database\Query\Builder $query
     * @param string $postPosttype Posttype của post chứa comment
     * @return \System\Database\Query\Builder
     */
    public function scopeByPostPosttype($query, $postPosttype)
    {
        return $query->where('post_posttype', $postPosttype);
    }

    /**
     * Scope: By post ID
     * 
     * @param \System\Database\Query\Builder $query
     * @param int $postId
     * @return \System\Database\Query\Builder
     */
    public function scopeByPost($query, $postId)
    {
        return $query->where('post_id', $postId);
    }

    /**
     * Scope: Top level comments (no parent)
     * 
     * @param \System\Database\Query\Builder $query
     * @return \System\Database\Query\Builder
     */
    public function scopeTopLevel($query)
    {
        return $query->where('par_comment', 0);
    }

    /**
     * Scope: By user
     * 
     * @param \System\Database\Query\Builder $query
     * @param int $userId
     * @return \System\Database\Query\Builder
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
}
