let data = []
let meta_image_frame;
let meta_image_preview = jQuery('.image-preview')
let previewHeader = document.querySelector('.preview-header');

/**
 * Get Diff from 1st array with 2nd array
 * @param {Array} arr1
 * @param {Array} arr2
 * @param {String} prop
 */
function getDiffFrom(arr1, arr2, prop) {
    return arr1.filter(item => {
        return !arr2.some(arr => {
            return item[prop] === arr[prop]
        })
    })
}

/**
 * Remove Duplicate data from array based on the prop passed in second params
 * @param {Array} arr
 * @param {String} prop
 */
function removeDuplicate(arr, prop) {
    return arr.filter((obj, index, self) => {
        return self.map(mapObj => mapObj[prop]).indexOf(obj[prop]) === index
    })
}

/**
 * Function to render the HTML
 * @param {Document Object Model} to
 * @param {JSON} data
 */
function renderHTML(to, data) {
    let html = '';

    if(data.length === 0) to.html(html)

    data.forEach(media => {
        html += `
            <div class="preview-group">
                <div class="preview-link">
                    <label>Link</label>
                    <input name="image_gallery[]" type="text" value="${media.url}" readonly>
                </div>
                <div class="image-show">
                    <img src="${media.url}">
                    <span onclick="deletePhoto(${media.id})" class="dashicons dashicons-no-alt"></span>
                </div>
            </div>
        `;
    })
    let url = window.location.href;
    let params = new URLSearchParams(url);
    let isEdit = params.get('action') ? true : false

    if(isEdit) {
        to.append(html)
    }else{
        to.html(html)
    }

    // displayHeader();
}

/**
 * Return new Array without the certain id passed in the first params
 * @param {Int} id
 */
function deletePhoto(id) {
    data = data.filter(item => {
        return item.id !== id
    })

    renderHTML(meta_image_preview, data)
}

/**
 * Remove the DOM
 * @param {event} e
 */
function hardDelete(e) {
    e.target.parentNode.parentNode.remove()
    displayHeader();
}

/**\
 * Display and remove the header
 */
function displayHeader() {
    let childCount = document.querySelectorAll('.preview-group').length;

    if(childCount === 0) previewHeader.style.display = 'none';
    else if(childCount > 0) previewHeader.style.display = 'block';
}

/**
 * WP Library Frame
 */
jQuery(document).ready(function($) {
    $('.image-upload').click(function(e) {
        e.preventDefault();

        // if the frame already exist reopen it
        if(meta_image_frame) {
            meta_image_frame.open();
            return;
        }

        // set up the media library frame
        meta_image_frame = wp.media.frames.meta_image_frame = wp.media({
            title: 'Choose Image',
            button: { text: 'Choose' },
            multiple: true
        })

        // runs when an select button clicked
        meta_image_frame.on('select', function() {
            // grabs the attachment selection and creates a JSON
            let media_attachment = meta_image_frame.state().get('selection').toJSON();
            media_attachment.forEach(media => data.push(media))

            data = removeDuplicate(data, 'id');

            renderHTML(meta_image_preview, data)
        })
        // opens the media library frame
        meta_image_frame.open();
    })
})