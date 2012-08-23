STUDIP.jsupdate_enable = true;
STUDIP.FF = {
    periodicalPushData: function () {
        return {
            'seminar_id': jQuery("#seminar_id").val(),
            'last_check': jQuery('#last_check').val()
        };
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

            jQuery('#last_check').val(Math.floor(new Date().getTime() / 1000));
        }
        STUDIP.FF.updateTimestamps();
    },
    alreadyThreadWriting: false,
    newPosting: function () {
        if (STUDIP.FF.alreadyThreadWriting) {
            return;
        }
        if (jQuery.trim(jQuery("#new_posting").val())) {
            STUDIP.FF.alreadyThreadWriting = true;
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
                },
                complete: function () {
                    STUDIP.FF.alreadyThreadWriting = false;
                }
            });
        }
    },
    alreadyWriting: false,
    write: function (textarea) {
        var content = jQuery(textarea).val();
        var thread = jQuery(textarea).closest("li").attr("id");

        if (!content || STUDIP.FF.alreadyWriting) {
            return;
        }
        STUDIP.FF.alreadyWriting = true;

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
            },
            complete: function () {
                STUDIP.FF.alreadyWriting = false;
            }
        });
    },
    insertComment: function (thread, posting_id, mkdate, comment) {
        if (jQuery("#" + posting_id).length) {
            if (jQuery("#" + posting_id + " textarea.corrector").length === 0) {
                if (jQuery("#" + posting_id + " .content").html() !== jQuery(comment).find(".content").html()) {
                    //nur wenn es Unterschiede gibt
                    jQuery("#" + posting_id).replaceWith(comment);
                }
            }
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
        STUDIP.FF.updateTimestamps();
    },
    insertThread: function (posting_id, mkdate, comment) {
        if (jQuery("#" + posting_id).length) {
            if (jQuery("#" + posting_id + " > .content_column textarea.corrector").length === 0) {
                var new_version = jQuery(comment);
                jQuery("#" + posting_id + " > .content_column .content").html(new_version.find(".content").html());
                new_version.remove();
            }
        } else {
            if (jQuery("#forum_threads > li").length === 0) {
                jQuery(comment).appendTo("#forum_threads").hide().fadeIn();
            } else {
                var already_inserted = false;
                jQuery("#forum_threads > li[id]").each(function (index, li) {
                    if (!already_inserted && jQuery(li).attr("mkdate") < mkdate) {
                        jQuery(comment).insertBefore(li).hide().fadeIn();
                        STUDIP.FF.updateTimestamps();
                        already_inserted = true;
                    }
                });
                if (!already_inserted) {
                    jQuery(comment).appendTo("#forum_threads").hide().fadeIn();
                }
            }
        }
        STUDIP.FF.makeTextareasAutoresizable();
        STUDIP.FF.updateTimestamps();
    },
    startEditingComment: function () {
        var id = jQuery(this).closest("li").attr("id");
        jQuery.ajax({
            'url': STUDIP.ABSOLUTE_URI_STUDIP + jQuery("#base_url").val() + "/get_source",
            'data': {
                'topic_id': id,
                'cid': jQuery("#seminar_id").val()
            },
            'success': function (source) {
                jQuery("#" + id).find(".content_column .content").first().html(
                    jQuery('<textarea class="corrector"/>').val(source).focus()
                );
                jQuery("#" + id).find(".corrector").focus();
                STUDIP.FF.makeTextareasAutoresizable();
                jQuery("#" + id).find(".corrector").trigger("keydown");
            }
        });

    },
    submittingEditedPostingStarted: false,
    submitEditedPosting: function (textarea) {
        var id = jQuery(textarea).closest("li").attr("id");
        if (STUDIP.FF.submittingEditedPostingStarted) {
            return;
        }
        STUDIP.FF.submittingEditedPostingStarted = true;
        if (jQuery("#" + id).attr("data-autor") === jQuery("#user_id").val()
                || window.confirm(jQuery("#editing_question").text())) {
            STUDIP.FF.submittingEditedPostingStarted = false;
            jQuery.ajax({
                'url': STUDIP.ABSOLUTE_URI_STUDIP + jQuery("#base_url").val() + "/edit_posting",
                'data': {
                    'topic_id': id,
                    'content': jQuery(textarea).val(),
                    'cid': jQuery("#seminar_id").val()
                },
                'type': "post",
                'success': function (new_content) {
                    if (new_content) {
                        jQuery("#" + id + " > .content_column .content").html(new_content);
                    } else {
                        jQuery("#" + id).fadeOut(function () {jQuery("#" + id).remove();});
                    }
                }
            });
        } else {
            STUDIP.FF.submittingEditedPostingStarted = false;
            jQuery.ajax({
                'url': STUDIP.ABSOLUTE_URI_STUDIP + jQuery("#base_url").val() + "/refresh_posting",
                'data': {
                    'topic_id': id,
                    'cid': jQuery("#seminar_id").val()
                },
                'success': function (new_content) {
                    jQuery("#" + id + " > .content_column .content").html(new_content);
                }
            });
        }
    },
    makeTextareasAutoresizable: function () {
        jQuery("#forum_threads textarea:not(.autoresize), #new_posting:not(.autoresize)").autoResize({
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
        }).addClass("autoresize")
            .bind('dragover dragleave', function (event) {
            jQuery(this).toggleClass('hovered', event.type === 'dragover');
            return false;
        }).each(function (index, textarea) {
            textarea.addEventListener("drop", function (event) {
                event.preventDefault();
                var files = 0;
                var file_info = event.dataTransfer.files;
                var data = new FormData();
                jQuery.each(file_info, function (index, file) {
                    if (file.size > 0) {
                        data.append(index, file);
                        files += 1;
                    }
                });
                if (files > 0) {
                    jQuery(textarea).addClass("uploading");
                    jQuery.ajax({
                        'url': STUDIP.ABSOLUTE_URI_STUDIP + jQuery("#base_url").val() + "/post_files",
                        'data': data,
                        'cache': false,
                        'contentType': false,
                        'processData': false,
                        'type': 'POST',
                        'xhr': function () {
                            var xhr = jQuery.ajaxSettings.xhr();
                            //workaround for FF<4 https://github.com/francois2metz/html5-formdata
                            if (data.fake) {
                                xhr.setRequestHeader("Content-Type", "multipart/form-data; boundary=" + data.boundary);
                                xhr.send = xhr.sendAsBinary;
                            }
                            return xhr;
                        },
                        'success': function (json) {
                            if (typeof json.inserts === "object") {
                                jQuery.each(json.inserts, function (index, text) {
                                    jQuery(textarea).val(jQuery(textarea).val() + " " + text);
                                });
                            }
                            if (typeof json.errors === "object") {
                                alert(json.errors.join("\n"));
                            } else if (typeof json.inserts !== "object") {
                                alert("Fehler beim Dateiupload.");
                            }
                            jQuery(textarea).trigger("keydown");
                        },
                        'complete': function () {
                            jQuery(textarea).removeClass("hovered").removeClass("uploading");
                        }
                    });
                }
            }, false);
        });
    },
    updateTimestamps: function () {
        var date = new Date();
        var now_seconds = Math.floor(date.getTime() / 1000);
        jQuery("#forum_threads .posting .time").each(function () {
            var new_text = "";
            var posting_time = parseInt(jQuery(this).attr("data-timestamp"));
            var diff = now_seconds - posting_time;
            if (diff < 86400) {
                if (diff < 2 * 60 * 60) {
                    if (Math.floor(diff / 60) === 0) {
                        new_text = "Vor wenigen Sekunden";
                    }
                    if (Math.floor(diff / 60) === 1) {
                        new_text = "Vor einer Minute";
                    }
                    if (Math.floor(diff / 60) > 1) {
                        new_text = "Vor " + Math.floor(diff / 60) + " Minuten";
                    }
                } else {
                    new_text = "Vor " + Math.floor(diff / (60 * 60)) + " Stunden";
                }
            } else {
                if (Math.floor(diff / 86400) < 8) {
                    if (Math.floor(diff / 86400) === 1) {
                        new_text = "Vor einem Tag";
                    } else {
                        new_text = "Vor " + Math.floor(diff / 86400) + " Tagen";
                    }
                } else {
                    date = new Date(posting_time * 1000);
                    new_text = date.getDate() + "." + (date.getMonth() + 1) + "." + date.getFullYear();
                }
            }
            if (jQuery(this).text() !== new_text) {
                jQuery(this).text(new_text);
            }
        });
        if (window.Touch || jQuery.support.touch) {
            //Touch support for devices with no hover-capability
            jQuery("#forum_threads .posting .time").css({
                "visibility": "visible"
            });
        }
    }

};

jQuery(STUDIP.FF.updateTimestamps);

jQuery("#threadwriter > textarea").live("keydown", function (event) {
    if (event.keyCode === 13 && !event.altKey && !event.ctrlKey && !event.shiftKey) {
        STUDIP.FF.newPosting();
        event.preventDefault();
    }
});
jQuery("#forum_threads textarea.corrector").live("keydown", function (event) {
    if (event.keyCode === 13 && !event.altKey && !event.ctrlKey && !event.shiftKey) {
        STUDIP.FF.submitEditedPosting(this);
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
            'cid': jQuery("#seminar_id").val()
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
    jQuery("#forum_threads a.edit").live("click", STUDIP.FF.startEditingComment);
    jQuery("#forum_threads textarea.corrector").live("blur", function () {STUDIP.FF.submitEditedPosting(this);});
});

jQuery(window.document).bind('scroll', function (event) {
    if ((jQuery(window).scrollTop() + jQuery(window).height() > jQuery(window.document).height() - 500)
            && (jQuery("#forum_threads > li.more").length > 0)) {
        //nachladen
        jQuery("#forum_threads > li.more").removeClass("more").addClass("loading");
        jQuery.ajax({
            url: STUDIP.ABSOLUTE_URI_STUDIP + jQuery("#base_url").val() + "/more_postings",
            data: {
                'before': jQuery("#forum_threads > li:nth-last-child(2)").attr("id"),
                'cid': jQuery("#seminar_id").val()
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