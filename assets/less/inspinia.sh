#! /bin/bash

lessc ./inspinia/style.less ../css/inspinia.css
lessc --clean-css ../css/inspinia.css ../css/inspinia.min.css
