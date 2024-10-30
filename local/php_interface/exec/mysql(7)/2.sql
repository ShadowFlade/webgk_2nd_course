select b_user.email,b_user.id as USER_ID, COUNT(b_sale_basket.PRODUCT_ID) as PRODUCT_COUNT from b_sale_basket
    left join b_sale_fuser on b_sale_fuser.ID = b_sale_basket.FUSER_ID
    left join b_user on b_user.ID = b_sale_fuser.user_id;