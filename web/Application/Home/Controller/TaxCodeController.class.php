<?php
/**
 * Created by PhpStorm.
 * User: hongwang
 * Date: 2019/4/11
 * Time: 11:12
 */
namespace Home\Controller;
use Home\Common\FIdConst;
use Home\Service\UserService;


class TaxCodeController extends PSIBaseController{

    public function taxCode(){
        $us = new UserService();
        if ($us->hasPermission(FIdConst::TAX_CODE)) {
            $this->initVar();

            $this->assign("title", "基础税控编码信息");

            $this->display();
        } else {
            $this->gotoLoginPage("/Home/TaxCode/taxCode");
        }
    }

    public function taxCodeList(){
        $db=M();
        if(IS_POST){
                $name=trim(I("post.name"));
                $code=trim(I("post.code"));
                $cate_name=trim(I("post.cate_name"));
                $start=I("post.start");
                $limit=I("post.limit");
                $data=[];
            $sql="select * from t_tax_code where 1 = 1 ";
            if($name){
                $sql.=" AND name like '%s'";
                $data[]="%$name%";
            }
            if($code){
                $sql.=" AND merge_code like '%s'";
                $data[]="%$code%";
            }
            if($cate_name){
                $sql.=" AND cate_name like '%s'";
                $data[]="%$cate_name%";
            }
            $count=$db->query($sql,$data);
            $sql.=" limit %d,%d";
            $data[]=$start;
            $data[]=$limit;
            $result=$db->query($sql,$data);

            $result=[
                "dataList" => $result,
                "totalCount" => count($count)
            ];
            $this->ajaxReturn($result);
        }
    }
    public function addTaxCode(){
        $db=M();
        if(IS_POST){
            $id=I("post.id");
            $code_one=trim(I("post.code_one"));
            $code_two=trim(I("post.code_two"));
            $code_three=trim(I("post.code_three"));
            $code_four=trim(I("post.code_four"));
            $code_five=trim(I("post.code_five"));
            $code_six=trim(I("post.code_six"));
            $code_seven=trim(I("post.code_seven"));
            $code_eight=trim(I("post.code_eight"));
            $code_nine=trim(I("post.code_nine"));
            $code_ten=trim(I("post.code_ten"));
            $merge_code=trim(I("post.merge_code"));
            $merge_code_history=trim(I("post.merge_code_history"));
            $goods_name=trim(I("post.goods_name"));
            $goods_cate_name=trim(I("post.goods_cate_name"));
            $memo=trim(I("post.memo"));
            if($merge_code != $merge_code_history){
                //查询税务编码是否重复
                $sql="select id from t_tax_code where merge_code = '%s'";
                $res=$db->query($sql,$merge_code);
                if($res[0]["id"]){
                    $this->ajaxReturn($this->bad("税务编码重复,请检查编码是否正确"));
                }
                $data["code_one"]=$code_one;
                $data["code_two"]=$code_two;
                $data["code_three"]=$code_three;
                $data["code_four"]=$code_four;
                $data["code_five"]=$code_five;
                $data["code_six"]=$code_six;
                $data["code_seven"]=$code_seven;
                $data["code_eight"]=$code_eight;
                $data["code_nine"]=$code_nine;
                $data["code_ten"]=$code_ten;
                $data["merge_code"]=$merge_code;
            }

                $data["name"]=$goods_name;
                $data["cate_name"]=$goods_cate_name;
                $data["memo"]=$memo;
                $data["date_created"]=date("Y-m-d H:i:s",time());

                //有值就是新增无值就是编辑
                if(!$id){
                    $id=M("t_tax_code")->add($data);
                    if($id == false){
                        $this->ajaxReturn($this->bad("添加失败,请重试"));
                    }
                }else{
                    $update=M("t_tax_code")->where("id=".$id)->save($data);
                    if($update == false){
                        $this->ajaxReturn($this->bad("修改失败,请重试"));
                    }
                }


            $result=$this->ok($id);
            $this->ajaxReturn($result);
        }
    }

    protected function ok($id = null) {
        if ($id) {
            return array(
                "success" => true,
                "id" => $id
            );
        } else {
            return array(
                "success" => true
            );
        }
    }
    protected function bad($msg) {
        return array(
            "success" => false,
            "msg" => $msg
        );
    }
}