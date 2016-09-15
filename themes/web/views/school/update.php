<?php $this->widget('Breadcrumb', array(
    'breadcrumbs' => array(
        'Quản lý School' => array('index'),
        'Sửa School'
    )
));
?>
<style type="text/css">
    input, textarea, .uneditable-input {
        width: 70%;
    }

    .cke_reset {
        width: 100% !important;
    }
</style>
<div class="portlet box blue">
    <div class="portlet-title">
        <h4><i class="icon-edit"></i>Sửa School</h4>
    </div>
    <div class="portlet-body form">
        <div class="form-horizontal form-view">
            <?php $form = $this->beginWidget('CActiveForm', array(
                'id'                   => 'cactive-form',
                'enableAjaxValidation' => FALSE,
                'htmlOptions'          => array(
                    'enctype' => 'multipart/form-data',
                    'role'    => 'form',
                ),
            )); ?>
            <h3>Thông tin</h3>

            <div class="row-fluid">
                <div class="span12">
                    <div class="row-fluid">
                        <div class="row-fluid">
                            <div class="span6- " style="margin-left: 2.5641%">
                                <div class="form-group">
                                    <?php echo $form->labelEx($model, 'name', array(
                                        'class' => 'control-label col-md-4 col-xs-12',
                                    )); ?>
                                    <div class="col-md-8 col-xs-12">
                                        <?php echo $form->textField($model, 'name', array(
                                            'size'        => 60,
                                            'maxlength'   => 255,
                                            'placeholder' => 'Name',
                                        )); ?>
                                        <?php echo $form->error($model, 'name'); ?>
                                    </div>
                                </div>
                            </div>

                            <div class="span6- ">
                                <div class="form-group">
                                    <?php echo $form->labelEx($model, 'logo', array(
                                        'class' => 'control-label col-md-4 col-xs-12',
                                    )); ?>
                                    <div class="col-md-8 col-xs-12">
                                        <input type="file" class="form-control-" name="image" id="myfile"/>
                                        <?php echo $form->error($model, 'logo'); ?>
                                    </div>
                                </div>
                            </div>

                            <div class="span6- ">
                                <div class="form-group">
                                    <?php echo $form->labelEx($model, 'description', array(
                                        'class' => 'control-label col-md-4 col-xs-12',
                                    )); ?>
                                    <div class="col-md-8 col-xs-12">
                                        <?php echo $form->textArea($model, 'description', array('class' => 'form-control')); ?>
                                    </div>
                                </div>
                            </div>

                            <div class="span12- ">
                                <div class="form-group">
                                    <?php echo $form->labelEx($model, 'popup_content', array(
                                        'class' => 'control-label col-md-4 col-xs-12',
                                    )); ?>
                                    <div class="col-md-8 col-xs-12" style="width: 80%">
                                        <?php $this->widget('application.extensions.editMe.widgets.ExtEditMe', array(
                                            'model'                     => $model,
                                            'attribute'                 => 'popup_content',
                                            'htmlOptions'               => array(
                                                'rows'  => 3,
                                                'class' => 'form-control-',
                                            ),
//                                            'filebrowserImageUploadUrl' => Yii::app()->baseUrl . '/static/kcfinder/upload.php?type=images&cms=yii',
                                            'filebrowserImageBrowseUrl' => Yii::app()->baseUrl . '/static/kcfinder/browse.php?type=images',
                                            'toolbar'                   => array(
                                                array('Source', '-',
                                                    'Bold', 'Italic', 'Underline', 'Strike', '-',
                                                    'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock', '-',
                                                    'NumberedList', 'BulletedList', '-',
                                                    'Outdent', 'Indent', 'Blockquote', '-',
                                                    'Link', 'Unlink', '-'),
                                                array('Format', 'Image', 'Youtube', 'Video', 'Table', 'Smiley', 'SpecialChar', '-',
                                                    'TextColor', 'BGColor', '-',
                                                    'Undo', 'Redo', '-',
                                                    'Maximize'),
                                            ),
                                        )); ?>
                                    </div>
                                </div>
                            </div>

                            <div class="span6- ">
                                <div class="form-group">
                                    <?php echo $form->labelEx($model, 'sub_domain', array(
                                        'class' => 'control-label col-md-4 col-xs-12',
                                    )); ?>
                                    <div class="col-md-8 col-xs-12">
                                        <?php echo $form->textField($model, 'sub_domain', array(
                                            'size'      => 60,
                                            'maxlength' => 255,
                                        )); ?>
                                        <?php echo $form->error($model, 'sub_domain'); ?>
                                    </div>
                                </div>
                            </div>

                            <div class="span6- ">
                                <div class="form-group">
                                    <?php echo $form->labelEx($model, 'phone', array(
                                        'class' => 'control-label col-md-4 col-xs-12',
                                    )); ?>
                                    <div class="col-md-8 col-xs-12">
                                        <?php echo $form->textField($model, 'phone', array(
                                            'size'      => 60,
                                            'maxlength' => 255,
                                        )); ?>
                                        <?php echo $form->error($model, 'phone'); ?>
                                    </div>
                                </div>
                            </div>

                            <div class="span6- ">
                                <div class="form-group">
                                    <?php echo $form->labelEx($model, 'website', array(
                                        'class' => 'control-label col-md-4 col-xs-12',
                                    )); ?>
                                    <div class="col-md-8 col-xs-12">
                                        <?php echo $form->textField($model, 'website', array(
                                            'size'      => 60,
                                            'maxlength' => 255,
                                        )); ?>
                                        <?php echo $form->error($model, 'website'); ?>
                                    </div>
                                </div>
                            </div>

                            <div class="span6- ">
                                <div class="form-group">
                                    <label for="Subscriber_is_active" class="control-label col-md-4 col-xs-12">Trạng thái</label>
                                    <div class="col-md-8 col-xs-12">
                                        <?php echo $form->dropdownList($model, 'is_active', array(0 => 'Chưa kích hoạt', 1 => 'Đã kích hoạt')); ?>

                                        <?php echo $form->error($model, 'is_active'); ?>
                                    </div>
                                </div>
                            </div>

                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn blue">
                                <i class="icon-pencil"></i> Cập nhật
                            </button>
                            <button type="button" class="btn" onclick="window.location.href='<?php echo Yii::app()->createUrl('school/index') ?>'">
                                Back
                            </button>
                        </div>

                    </div>
                </div>

            </div>
            <?php $this->endWidget(); ?>
        </div>
    </div>
</div>
<script>
    <?php if(Yii::app()->user->hasFlash('success')):?>
    alert('<?php echo Yii::app()->user->getFlash('success'); ?>');
    <?php endif;?>

    <?php if(Yii::app()->user->hasFlash('error')):?>
    alert('<?php echo Yii::app()->user->getFlash('error'); ?>');
    <?php endif;?>
</script>