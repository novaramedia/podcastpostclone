fillClone = function(data) {
  var post = data.posts[0];

  console.log(post);

  // Set title
  document.getElementById('title').focus();
  document.getElementById('title').value = post.title;

  // Set content
  // For Visual editor
  if (document.getElementById('content-tmce')) {
    window.switchEditors.go('content', 'html');
    document.getElementById('content').value = post.short_desc;
    window.switchEditors.go('content', 'tmce');
  // For Text editor
  } else {
    document.querySelector('.wp-editor-area').value = post.short_desc;
  }

  // Set tags
  var tagsList = '';
  post.tags.forEach(function(tag, index, tags) {
    tagsList += tag + ", ";
  });

  document.getElementById('new-tag-post_tag').value = tagsList;

  // Set permalink meta
  document.getElementById('_cmb_redirect').value = post.permalink;

  // Set FM category
  jQuery('.popular-category').each(function(cat, index) {

    var input = jQuery(this).find('label:contains( FM)').children('input');

    if (input) {
      input.click();
    }

  });
}

document.addEventListener('DOMContentLoaded', function() {
  var suckDataButton = document.getElementById('suck-vimeo-data');
  suckDataButton.addEventListener('click', function(e) {
    e.preventDefault();

    // Turn on spinner
    document.getElementById('globie-spinner').style.display = 'inline-block';

    // Get video data

    jQuery.getJSON('http://novaramedia.com/api/podcast-tool/?callback=?', function(data) {

      if (data.error) {

        jQuery.getJSON('http://novaramedia.com/api/fm/?callback=?', function(data) {

          if (!data.error) {

            fillClone(data);

          } else {

            alert(data.error);

          }

          document.getElementById('globie-spinner').style.display = "none";

      });

      } else {

        fillClone(data);

        document.getElementById('globie-spinner').style.display = "none";

      }

    });
  });
});