select b_iblock_element_property.value as ARTNUMBER_VALUE, count(*)
from b_catalog_product
         left join b_iblock_element on b_catalog_product.id = b_iblock_element.id
         left join b_iblock_property on b_iblock_property.iblock_id = b_iblock_element.iblock_id
         left join b_iblock_element_property on b_iblock_element_property.iblock_element_id = b_catalog_product.id
where b_iblock_property.code = 'ARTNUMBER'
GROUP BY ARTNUMBER_VALUE
having count(*) > 1;