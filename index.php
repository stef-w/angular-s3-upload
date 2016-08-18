<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Basebuilder bucket file upload</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=edge"/>

    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.6.3/css/font-awesome.min.css"/>
    <link href='https://fonts.googleapis.com/css?family=Dosis:400,700' rel='stylesheet' type='text/css'>
</head>
<body>

<div ng-app="basebuilderBucketUploader" class="container">

    <div class="row"  ng-controller="UploadController">
        <div class="col-md-4">
            <h1>Config</h1>
            <div class="form-group">
                <label>Key</label>
                <input type="text" class="form-control" ng-model="config.access_key">
            </div>
            <div class="form-group">
                <label>secret</label>
                <input type="text" class="form-control" ng-model="config.secret_key">
            </div>
            <div class="form-group">
                <label>bucket</label>
                <input type="text" class="form-control" ng-model="config.bucket">
            </div>
        </div>
        <div class="col-md-8">
            <h1>Basebuilder bucket uploader</h1>
            <div>
                <div class="form-group">
                    <input class="form-control" type="file" name="file" file></input>
                </div>
                <div class="progress">
                    <div class="progress-bar" role="progressbar" aria-valuenow="{{ uploadProgress }}" aria-valuemin="0"
                         aria-valuemax="100" style="width: {{ uploadProgress }}%;">
                        {{ uploadProgress == 0 ? '' : uploadProgress + '%' }}
                    </div>
                </div>
                <a class="btn btn-primary btn-block btn-lg" ng-click="upload()">Upload</a>
            </div>
        </div>
    </div>
    <div class="row">

        <div class="col-md-4">
            <h2>Cors configuration</h2>
            <pre><?print htmlentities(file_get_contents('cors-config.xml')) ?></pre>
        </div>
        <div class="col-md-4">
            <h2>crossdomain.xml</h2>
            <p>Should be placed in the bucket's root</p>
            <pre><?print htmlentities(file_get_contents('crossdomain.xml')) ?></pre>
        </div>
        <div class="col-md-4">
            <h2>IAM policy</h2>
            <pre><?print htmlentities(file_get_contents('iam-policy.json')) ?></pre>
        </div>

    </div>

</div>

<script src="bower_components/angular/angular.js"></script>
<script src="bower_components/angular-sanitize/angular-sanitize.min.js"></script>
<script src="bower_components/aws-sdk-js/dist/aws-sdk.min.js"></script>
<script>

    var app = angular.module('basebuilderBucketUploader', ['controllers', 'directives']);

    var controllers = angular.module('controllers', []);

    controllers.controller('UploadController', ['$scope', function ($scope) {
        $scope.sizeLimit = 10585760; // 10MB in Bytes
        $scope.uploadProgress = 0;
        $scope.config = {
            access_key: 'AKIAIIQKIXTBKJOUIE6Q',
            secret_key: 'ask stef!',
            bucket: 'stefw-angular-upload'
        };
        

        $scope.region = 'eu-west-1';

        $scope.upload = function () {
            AWS.config.update({accessKeyId: $scope.config.access_key, secretAccessKey: $scope.config.secret_key});
            AWS.config.region = $scope.region;
            var bucket = new AWS.S3({params: {Bucket: $scope.config.bucket}});

            if ($scope.file) {
                // Perform File Size Check First
                var fileSize = Math.round(parseInt($scope.file.size));
                // Prepend Unique String To Prevent Overwrites
                var uniqueFileName = $scope.uniqueString() + '-' + $scope.file.name;

                var params = {
                    Key: uniqueFileName,
                    ContentType: $scope.file.type,
                    Body: $scope.file,
                    ServerSideEncryption: 'AES256'
                };

                bucket.putObject(params, function (err, data) {
                    if (err) {
                        alert(err.message, err.code);
                        return false;
                    }
                    else {
                        // Upload Successfully Finished
                        alert('File Uploaded Successfully: Done');

                        // Reset The Progress Bar
                        setTimeout(function () {
                            $scope.uploadProgress = 0;
                            $scope.$digest();
                        }, 4000);
                    }
                }).on('httpUploadProgress', function (progress) {
                    $scope.uploadProgress = Math.round(progress.loaded / progress.total * 100);
                    $scope.$digest();
                });
            }
            else {
                // No File Selected
                alert('Please select a file to upload');
            }
        };

        $scope.fileSizeLabel = function () {
            // Convert Bytes To MB
            return Math.round($scope.sizeLimit / 1024 / 1024) + 'MB';
        };

        $scope.uniqueString = function () {
            var text = "";
            var possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";

            for (var i = 0; i < 8; i++) {
                text += possible.charAt(Math.floor(Math.random() * possible.length));
            }
            return text;
        }

    }]);

    var directives = angular.module('directives', []);
    directives.directive('file', function () {
        return {
            restrict: 'AE',
            scope: {
                file: '@'
            },
            link: function (scope, el, attrs) {
                el.bind('change', function (event) {
                    var files = event.target.files;
                    var file = files[0];
                    scope.file = file;
                    scope.$parent.file = file;
                    scope.$apply();
                });
            }
        };
    });

</script>

</body>
</html>
