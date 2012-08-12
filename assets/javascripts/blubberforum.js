STUDIP.jsupdate_enable = true;
STUDIP.FF = {
    periodicalPushData: function () {
        return {'seminar_id': jQuery("#seminar_id").val() };
    },
    getNewPosts: function (data) {
        if (data.postings) {
            jQuery.each(data.postings, function (index, posting) {
                if (posting.root_id !== posting.posting_id) {
                    //comment
                    STUDIP.FF.insertComment(posting.root_id, posting.posting_id, posting.mkdate, posting.content);
                } else {
                    //thread
                    STUDIP.FF.insertThread(posting.posting_id, posting.mkdate, posting.content);
                }
            });
        }
    },
    newPosting: function () {
        if (jQuery.trim(jQuery("#new_posting").val())) {
            var content = jQuery("#new_posting").val();
            jQuery.ajax({
                url: STUDIP.ABSOLUTE_URI_STUDIP + jQuery("#base_url").val() + "/new_posting",
                data: {
                    'cid': jQuery("#seminar_id").val(),
                    'content': content
                },
                dataType: "json",
                type: "POST",
                success: function (reply) {
                    jQuery("#new_posting").val("").trigger("keydown");
                    STUDIP.FF.insertThread(reply.posting_id, reply.mkdate, reply.content);
                }
            });
        }
    },
    write: function (textarea) {
        var content = jQuery(textarea).val();
        var thread = jQuery(textarea).closest("li").attr("id");
        jQuery.ajax({
            url: STUDIP.ABSOLUTE_URI_STUDIP + jQuery("#base_url").val() + "/post",
            data: {
                'cid': jQuery("#seminar_id").val(),
                'thread': thread,
                'content': content
            },
            dataType: "json",
            type: "POST",
            success: function (reply) {
                jQuery(textarea).val("").trigger("keydown");
                STUDIP.FF.insertComment(thread, reply.posting_id, reply.mkdate, reply.content);
            }
        });
    },
    insertComment: function (thread, posting_id, mkdate, comment) {
        if (jQuery("#" + posting_id).length) {
            jQuery("#" + posting_id).replaceWith(comment);
        } else {
            if (jQuery("#" + thread + " ul.comments > li").length === 0) {
                jQuery(comment).appendTo("#" + thread + " ul.comments").hide().fadeIn();
            } else {
                var already_inserted = false;
                jQuery("#" + thread + " ul.comments > li").each(function (index, li) {
                    if (!already_inserted && jQuery(li).attr("mkdate") > mkdate) {
                        jQuery(comment).insertBefore(li).hide().fadeIn();
                        already_inserted = true;
                    }
                });
                if (!already_inserted) {
                    jQuery(comment).appendTo("#" + thread + " ul.comments").hide().fadeIn();
                }
            }
        }
    },
    insertThread: function (posting_id, mkdate, comment) {
        if (jQuery("#" + posting_id).length) {
            var new_version = jQuery(comment);
            jQuery("#" + posting_id + " .content").html(new_version.find(".content").html());
            new_version.remove();
        } else {
            if (jQuery("#forum_threads > li").length === 0) {
                jQuery(comment).appendTo("#forum_threads").hide().fadeIn();
            } else {
                var already_inserted = false;
                jQuery("#forum_threads > li[id]").each(function (index, li) {
                    if (!already_inserted && jQuery(li).attr("mkdate") < mkdate) {
                        jQuery(comment).insertBefore(li).hide().fadeIn();
                        already_inserted = true;
                    }
                });
                if (!already_inserted) {
                    jQuery(comment).appendTo("#forum_threads").hide().fadeIn();
                }
            }
        }
        STUDIP.FF.makeTextareasAutoresizable();
    },
    makeTextareasAutoresizable: function () {
        jQuery(".writer textarea:not(.autoresize), #new_posting:not(.autoresize)").autoResize({
            // On resize:
            onResize : function() {
                $(this).css({opacity: 0.8});
            },
            // After resize:
            animateCallback : function() {
                $(this).css({opacity:1});
            },
            // Quite slow animation:
            animateDuration: 300,
            // More extra space:
            extraSpace: 0
        }).addClass("autoresize");
    }
};

jQuery("#threadwriter > textarea").live("keydown", function (event) {
    if (event.keyCode === 13 && !event.altKey && !event.ctrlKey && !event.shiftKey) {
        STUDIP.FF.newPosting();
        event.preventDefault();
    }
});
jQuery(".writer > textarea").live("keydown", function (event) {
    if (event.keyCode === 13 && !event.altKey && !event.ctrlKey && !event.shiftKey) {
        STUDIP.FF.write(this);
        event.preventDefault();
    }
});
jQuery("#forum_threads > li > ul.comments > li.more").live("click", function () {
    var thread_id = jQuery(this).closest("li[id]").attr("id");
    var li_more = this;
    jQuery.ajax({
        url: STUDIP.ABSOLUTE_URI_STUDIP + jQuery("#base_url").val() + "/more_comments",
        data: {
            'thread_id': thread_id,
            'seminar_id': jQuery("#seminar_id").val()
        },
        dataType: "json",
        success: function (data) {
            if (data.comments) {
                jQuery(li_more).remove();
                jQuery.each(data.comments, function (index, comment) {
                    STUDIP.FF.insertComment(thread_id, comment.posting_id, comment.mkdate, comment.content);
                });
            }
        }
    });
});
jQuery(function () {
    STUDIP.FF.makeTextareasAutoresizable();
    jQuery("#new_title").focus(function () {
        jQuery("#new_posting").fadeIn(function () {
            STUDIP.FF.makeTextareasAutoresizable();
        });
    });
});

jQuery(window.document).bind('scroll', function (event) {
    if ((jQuery(window).scrollTop() + jQuery(window).height() > jQuery(window.document).height() - 500)
            && (jQuery("#forum_threads > li.more").length > 0)) {
        //nachladen
        jQuery("#forum_threads > li.more").removeClass("more").addClass("loading");
        jQuery.ajax({
            url: STUDIP.ABSOLUTE_URI_STUDIP + jQuery("#base_url").val() + "/more_postings",
            data: {
                'before': jQuery("#forum_threads > li:nth-last-child(2)").attr("mkdate")
            },
            dataType: "json",
            success: function (response) {
                jQuery("#forum_threads > li.loading").remove();
                jQuery.each(response.threads, function (index, thread) {
                    STUDIP.FF.insertThread(thread.posting_id, thread.mkdate, thread.content);
                });
                if (response.more) {
                    jQuery("#forum_threads").append(jQuery('<li class="more">...</li>'));
                }
            }
        });
    }
});