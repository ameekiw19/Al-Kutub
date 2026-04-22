
// Only register plugins that are loaded by Template (image-preview, validate-size, validate-type)
FilePond.registerPlugin(
  FilePondPluginImagePreview,
  FilePondPluginFileValidateSize,
  FilePondPluginFileValidateType,
)

// Filepond: Basic
const basicFilepond = document.querySelector(".basic-filepond");
if (basicFilepond) {
FilePond.create(basicFilepond, {
  credits: null,
  allowImagePreview: false,
  allowMultiple: false,
  allowFileEncode: false,
  required: false,
  storeAsFile: true,
})
}

// Filepond: Multiple Files
const multipleFilesFilepond = document.querySelector(".multiple-files-filepond");
if (multipleFilesFilepond) {
FilePond.create(multipleFilesFilepond, {
  credits: null,
  allowImagePreview: false,
  allowMultiple: true,
  allowFileEncode: false,
  required: false,
  storeAsFile: true,
})
}

// Filepond: With Validation
const withValidationFilepond = document.querySelector(".with-validation-filepond");
if (withValidationFilepond) {
FilePond.create(withValidationFilepond, {
  credits: null,
  allowImagePreview: false,
  allowMultiple: true,
  allowFileEncode: false,
  required: true,
  acceptedFileTypes: ["image/png"],
  fileValidateTypeDetectType: (source, type) =>
    new Promise((resolve, reject) => {
      // Do custom type detection here and return with promise
      resolve(type)
    }),
  storeAsFile: true,
})
}

// Filepond: ImgBB with server property
const imgbbFilepond = document.querySelector(".imgbb-filepond");
if (imgbbFilepond) {
FilePond.create(imgbbFilepond, {
  credits: null,
  allowImagePreview: false,
  server: {
    process: (fieldName, file, metadata, load, error, progress, abort) => {
      // We ignore the metadata property and only send the file

      const formData = new FormData()
      formData.append(fieldName, file, file.name)

      const request = new XMLHttpRequest()
      // you can change it by your client api key
      request.open(
        "POST",
        "https://api.imgbb.com/1/upload?key=762894e2014f83c023b233b2f10395e2"
      )

      request.upload.onprogress = (e) => {
        progress(e.lengthComputable, e.loaded, e.total)
      }

      request.onload = function () {
        if (request.status >= 200 && request.status < 300) {
          load(request.responseText)
        } else {
          error("oh no")
        }
      }

      request.onreadystatechange = function () {
        if (this.readyState == 4) {
          if (this.status == 200) {
            let response = JSON.parse(this.responseText)

            Toastify({
              text: "Success uploading to imgbb! see console f12",
              duration: 3000,
              close: true,
              gravity: "bottom",
              position: "right",
              backgroundColor: "#4fbe87",
            }).showToast()
          } else {
            Toastify({
              text: "Failed uploading to imgbb! see console f12",
              duration: 3000,
              close: true,
              gravity: "bottom",
              position: "right",
              backgroundColor: "#ff0000",
            }).showToast()
          }
        }
      }

      request.send(formData)
    },
  },
  storeAsFile: true,
})
}

// Filepond: Image Preview
const imagePreviewFilepond = document.querySelector(".image-preview-filepond");
if (imagePreviewFilepond) {
FilePond.create(imagePreviewFilepond, {
  credits: null,
  allowImagePreview: true,
  allowImageFilter: false,
  allowImageExifOrientation: false,
  allowImageCrop: false,
  acceptedFileTypes: ["image/png", "image/jpg", "image/jpeg"],
  fileValidateTypeDetectType: (source, type) =>
    new Promise((resolve, reject) => {
      // Do custom type detection here and return with promise
      resolve(type)
    }),
  storeAsFile: true,
})
}

// Filepond: Image Crop
const imageCropFilepond = document.querySelector(".image-crop-filepond");
if (imageCropFilepond) {
FilePond.create(imageCropFilepond, {
  credits: null,
  allowImagePreview: true,
  allowImageFilter: false,
  allowImageExifOrientation: false,
  allowImageCrop: true,
  acceptedFileTypes: ["image/png", "image/jpg", "image/jpeg"],
  fileValidateTypeDetectType: (source, type) =>
    new Promise((resolve, reject) => {
      // Do custom type detection here and return with promise
      resolve(type)
    }),
  storeAsFile: true,
})
}

// Filepond: Image Exif Orientation
const imageExifFilepond = document.querySelector(".image-exif-filepond");
if (imageExifFilepond) {
FilePond.create(imageExifFilepond, {
  credits: null,
  allowImagePreview: true,
  allowImageFilter: false,
  allowImageExifOrientation: true,
  allowImageCrop: false,
  acceptedFileTypes: ["image/png", "image/jpg", "image/jpeg"],
  fileValidateTypeDetectType: (source, type) =>
    new Promise((resolve, reject) => {
      // Do custom type detection here and return with promise
      resolve(type)
    }),
  storeAsFile: true,
})
}

// Filepond: Image Filter
const imageFilterFilepond = document.querySelector(".image-filter-filepond");
if (imageFilterFilepond) {
FilePond.create(imageFilterFilepond, {
  credits: null,
  allowImagePreview: true,
  allowImageFilter: true,
  allowImageExifOrientation: false,
  allowImageCrop: false,
  imageFilterColorMatrix: [
    0.299, 0.587, 0.114, 0, 0, 0.299, 0.587, 0.114, 0, 0, 0.299, 0.587, 0.114,
    0, 0, 0.0, 0.0, 0.0, 1, 0,
  ],
  acceptedFileTypes: ["image/png", "image/jpg", "image/jpeg"],
  fileValidateTypeDetectType: (source, type) =>
    new Promise((resolve, reject) => {
      // Do custom type detection here and return with promise
      resolve(type)
    }),
  storeAsFile: true,
})
}

// Filepond: Image Resize
const imageResizeFilepond = document.querySelector(".image-resize-filepond");
if (imageResizeFilepond) {
FilePond.create(imageResizeFilepond, {
  credits: null,
  allowImagePreview: true,
  allowImageFilter: false,
  allowImageExifOrientation: false,
  allowImageCrop: false,
  allowImageResize: true,
  imageResizeTargetWidth: 200,
  imageResizeTargetHeight: 200,
  imageResizeMode: "cover",
  imageResizeUpscale: true,
  acceptedFileTypes: ["image/png", "image/jpg", "image/jpeg"],
  fileValidateTypeDetectType: (source, type) =>
    new Promise((resolve, reject) => {
      // Do custom type detection here and return with promise
      resolve(type)
    }),
  storeAsFile: true,
})
}
