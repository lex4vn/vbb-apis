<?php $this->widget('Breadcrumb', array(
    'breadcrumbs' => array(
        'Quản lý School' => array('index'),
        'Danh sách School'
    )
));
    $this->pageTitle = 'Danh sách School';
?>

<div class="portlet box blue">
    <div class="portlet-title">
        <h4><i class="icon-edit"></i>Danh sách School</h4>
    </div>
    <div class="portlet-body">
        <div class="clearfix">
            <div class="btn-group">
                <button id="sample_editable_1_new" class="btn green" onclick="window.location.href='<?php echo Yii::app()->createUrl('school/create') ?>'">
                    Thêm mới <i class="icon-plus"></i>
                </button>
            </div>
        </div>
        <div id="sample_editable_1_wrapper" class="dataTables_wrapper form-inline" role="grid">

            <div class="row-fluid">
                <div class="span12">
                    <?php $this->widget('zii.widgets.grid.CGridView', array(
                        'id'            => 'gridview-list',
                        'dataProvider'  => $model->search(),
                        'filter'        => $model,
                        'itemsCssClass' => 'table table-striped table-hover table-bordered dataTable',
                        'summaryText'   => '',
                        'htmlOptions'   => array(
                            'css' => 'portlet-title'
                        ),
                        'pager'         => array(
                            'nextPageCssClass'     => 'next',
                            'previousPageCssClass' => 'prev',
                            'selectedPageCssClass' => 'active',
                            'cssFile'              => Yii::app()->theme->baseUrl . '/assets/css/pager.css',
                            'nextPageLabel'        => 'Sau >',
                            'prevPageLabel'        => '< Trước',
                            'header'               => ''
                        ),
                        'pagerCssClass' => 'dataTables_paginate paging_bootstrap pagination',
                        'columns'       => array(
                            array(
                                'name'        => 'id',
                                'htmlOptions' => array(
                                    'style' => 'width: 50px; text-align:center;'
                                )
                            ),
                            array(
                                'name'        => 'logo',
                                'type'        => 'html',
                                'value'       => function ($data) {
                                    if ($data->logo != '')
                                        return "<img style='width: auto; height: 60px' src='http://backend.hocde.net/images/web/school/" . $data->logo . "' />";

                                    return '';
                                },
                                'htmlOptions' => array(
                                    'style' => 'width: 70px; text-align:center;'
                                ),
                                'filter'      => FALSE
                            ),
                            array(
                                'name'        => 'name',
                                'htmlOptions' => array(
                                    'style' => 'width: 200px; text-align:center;'
                                )
                            ),
                            array(
                                'name'        => 'website',
                                'htmlOptions' => array(
                                    'style' => 'width: 100px; text-align:center;'
                                )
                            ),
                            array(
                                'name'        => 'created_date',
                                'type'        => 'html',
                                'value'       => function ($data) {
                                    if ($data->created_date != '')
                                        return date('d/m/Y H:i', strtotime($data->created_date));

                                    return '';
                                },
                                'htmlOptions' => array(
                                    'style' => 'width: 70px; text-align:center;'
                                ),
                                'filter'      => FALSE
                            ),
                            array(
                                'name'        => 'is_active',
                                'type'        => 'raw',
                                'value'       => function ($data) {
                                    $icon   = $data->is_active == 1 ? "<i class=\"icon-check\"></i>" : "<i class=\"icon-check-empty\"></i>";
                                    $status = $data->is_active == 1 ? 0 : 1;

                                    return CHtml::link($icon, "javascript:;", array(
                                        'title'               => '',
                                        'class'               => '',
                                        'data-toggle'         => 'tooltip',
                                        'data-original-title' => 'Thay đổi trạng thái',
                                        'onclick'             => 'changeStatus(' . $data->id . ',' . $status . ');',
                                    ));

                                },
                                'htmlOptions' => array('width' => '50', 'style' => 'text-align: center;vertical-align:middle;'),
                            ),
                            array(
                                'header'             => '<a href="#">Thao tác</a>',
                                'class'              => 'CButtonColumn',
                                'deleteConfirmation' => "js:'Bạn có chắc chắn muốn xóa School \"'+$(this).parent().parent().children(':nth-child(2)').text()+'\"?'",
                                'template'           => '{update}{delete}',
                                'htmlOptions'        => array('style' => 'width:50px;padding:5px;text-align: center;'),
                                'buttons'            => array(
                                    'update' => array(
                                        'label'    => '<i class="icon-pencil icon-white"></i>',
                                        'imageUrl' => FALSE,
                                        'options'  => array(
                                            'title' => 'Sửa School này',
                                            'class' => 'btn mini yellow',
                                            'style' => 'margin:2px'
                                        )
                                    ),
                                    'delete' => array(
                                        'label'    => '<i class="icon-remove icon-white"></i>',
                                        'imageUrl' => FALSE,
                                        'options'  => array(
                                            'title' => 'Xóa School này',
                                            'class' => 'btn mini red',
                                            'style' => 'margin:2px'
                                        )
                                    )
                                )
                            ),
                        ),
                    )); ?>

                </div>
            </div>
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
<script type="text/javascript">
    function changeStatus(id, status) {
        $.ajax({
            type: "POST",
            url: '<?=Yii::app()->createUrl('school/changeStatus')?>',
            crossDomain: true,
            dataType: 'json',
            data: {id: id, status: status, 'YII_CSRF_TOKEN': '<?php echo Yii::app()->request->csrfToken?>'},
            success: function (result) {
                if (result === true) {
                    $('#gridview-list').yiiGridView('update', {
                        data: $(this).serialize()
                    });
                    return false;
                }
            }
        });
    }
</script>