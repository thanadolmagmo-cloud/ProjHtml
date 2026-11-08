-- =====================================================================
-- ฐานข้อมูลโปรเจกต์ "ระบบสารสนเทศท่องเที่ยวและสินค้าชุมชน จังหวัดหนองคาย"
-- วิธีใช้: phpMyAdmin > แท็บ Import เลือกไฟล์นี้ (หรือแท็บ SQL แล้ววางทั้งไฟล์)
-- คำเตือน: ไฟล์นี้ลบและสร้างตารางใหม่ทั้งหมด (รวมตารางสมาชิก) ใช้ครั้งแรกหรือเมื่อต้องการล้างข้อมูล
-- =====================================================================
CREATE DATABASE IF NOT EXISTS nongkhai CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE nongkhai;
SET NAMES utf8mb4;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS product_image;
DROP TABLE IF EXISTS community_product;
DROP TABLE IF EXISTS product_type;
DROP TABLE IF EXISTS place_image;
DROP TABLE IF EXISTS place;
DROP TABLE IF EXISTS place_type;
DROP TABLE IF EXISTS district;
DROP TABLE IF EXISTS information;
DROP TABLE IF EXISTS user;
SET FOREIGN_KEY_CHECKS = 1;

-- ตารางผู้ใช้ (admin / member)
CREATE TABLE user (
    username VARCHAR(20) PRIMARY KEY,
    password VARCHAR(50) NOT NULL,
    name VARCHAR(100) NOT NULL,
    usertype VARCHAR(10) NOT NULL DEFAULT 'member'
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- ตารางข้อมูลส่วนตัวสมาชิก
CREATE TABLE information (
    username VARCHAR(20) PRIMARY KEY,
    idcard CHAR(13) NOT NULL UNIQUE,
    membername VARCHAR(100) NOT NULL,
    nickname VARCHAR(50),
    sex VARCHAR(10),
    birthdate INT,
    birthmonth INT,
    birthyear VARCHAR(4),
    address TEXT,
    email VARCHAR(100),
    mobile VARCHAR(20),
    fav1 VARCHAR(100),
    fav2 VARCHAR(100),
    fav3 VARCHAR(100),
    CONSTRAINT fk_info_user FOREIGN KEY (username) REFERENCES user(username) ON DELETE CASCADE
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- ผู้ดูแลระบบเริ่มต้น  (username: admin / password: admin1234) ควรเปลี่ยนรหัสผ่านหลังเข้าระบบครั้งแรก
INSERT INTO user (username, password, name, usertype) VALUES ('admin', 'admin1234', 'ผู้ดูแลระบบ', 'admin');

-- 1. ตารางอำเภอ
CREATE TABLE district (
    district_id VARCHAR(10) PRIMARY KEY,
    district_name VARCHAR(100) NOT NULL
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- 2. ตารางประเภทสถานที่
CREATE TABLE place_type (
    place_type_id VARCHAR(10) PRIMARY KEY,
    place_type_name VARCHAR(100) NOT NULL
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- 3. ตารางสถานที่
CREATE TABLE place (
    place_id VARCHAR(10) PRIMARY KEY,
    place_name VARCHAR(150) NOT NULL,
    description TEXT,
    gps VARCHAR(50),
    district_id VARCHAR(10),
    place_type_id VARCHAR(10),
    CONSTRAINT fk_place_district
        FOREIGN KEY (district_id) REFERENCES district(district_id),
    CONSTRAINT fk_place_type
        FOREIGN KEY (place_type_id) REFERENCES place_type(place_type_id)
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- 4. ตารางรูปภาพสถานที่
CREATE TABLE place_image (
    place_image_id VARCHAR(10) PRIMARY KEY,
    link_file VARCHAR(255) NOT NULL,
    place_id VARCHAR(10),
    CONSTRAINT fk_place_image_place
        FOREIGN KEY (place_id) REFERENCES place(place_id)
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- 5. ตารางประเภทสินค้า
CREATE TABLE product_type (
    product_type_id VARCHAR(10) PRIMARY KEY,
    product_type_name VARCHAR(100) NOT NULL
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- 6. ตารางสินค้าชุมชน
CREATE TABLE community_product (
    product_id VARCHAR(10) PRIMARY KEY,
    product_name VARCHAR(150) NOT NULL,
    description TEXT,
    place_id VARCHAR(10),
    product_type_id VARCHAR(10),
    CONSTRAINT fk_product_place
        FOREIGN KEY (place_id) REFERENCES place(place_id),
    CONSTRAINT fk_product_type
        FOREIGN KEY (product_type_id) REFERENCES product_type(product_type_id)
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- 7. ตารางรูปภาพสินค้า
CREATE TABLE product_image (
    product_image_id VARCHAR(10) PRIMARY KEY,
    link_file VARCHAR(255) NOT NULL,
    product_id VARCHAR(10),
    CONSTRAINT fk_product_image_product
        FOREIGN KEY (product_id) REFERENCES community_product(product_id)
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- ข้อมูลเริ่มต้น: 9 อำเภอของจังหวัดหนองคาย
INSERT INTO district (district_id, district_name) VALUES
 ('D01','เมืองหนองคาย'), ('D02','ท่าบ่อ'), ('D03','โพนพิสัย'),
 ('D04','ศรีเชียงใหม่'), ('D05','สังคม'), ('D06','สระใคร'),
 ('D07','เฝ้าไร่'), ('D08','รัตนวาปี'), ('D09','โพธิ์ตาก');

-- ข้อมูลเริ่มต้น: ประเภทสถานที่ 7 ประเภท (สถานบริการเชื้อเพลิง = ปั๊มน้ำมัน)
INSERT INTO place_type (place_type_id, place_type_name) VALUES
 ('PT01','สถานที่ท่องเที่ยว'), ('PT02','ที่พัก'), ('PT03','สถานบริการเชื้อเพลิง (ปั๊มน้ำมัน)'),
 ('PT04','ร้านขายของชำ'), ('PT05','สถานที่ทางศาสนา'), ('PT06','สถานพยาบาล'),
 ('PT07','สถานีตำรวจ');

-- ข้อมูลเริ่มต้น: ประเภทสินค้า (ตัวอย่าง แก้ไขชื่อได้ตามต้องการ)
INSERT INTO product_type (product_type_id, product_type_name) VALUES
 ('PR01','อาหาร'), ('PR02','เครื่องดื่ม'), ('PR03','ผ้าและเครื่องแต่งกาย'),
 ('PR04','ของใช้ ของประดับตกแต่ง และของที่ระลึก'), ('PR05','สมุนไพรที่ไม่ใช่อาหาร');

-- ===== ข้อมูลสถานที่ 21 แห่ง และสินค้าชุมชน 3 รายการ (พิกัด = ลิงก์ Google Maps) =====

INSERT INTO place (place_id, place_name, description, gps, district_id, place_type_id) VALUES ('P001', 'พระธาตุหนองคาย', 'พระธาตุเก่าแก่โบราณที่มีอายุหลายร้อยปี เดิมตั้งอยู่บนริมฝั่งแม่น้ำโขง แต่กระแสน้ำกัดเซาะตลิ่งทำให้องค์พระธาตุพังทลายลงสู่แม่น้ำโขงเมื่อปี พ.ศ. 2390 ในช่วงน้ำลด (โดยเฉพาะฤดูแล้งประมาณเดือนกุมภาพันธ์ - พฤษภาคม) จะมองเห็นฐานองค์พระธาตุโผล่พ้นน้ำขึ้นมา รวมถึงมีการประดับธงทิวอย่างสวยงาม

องค์จำลอง (พระธาตุหล้าหนอง): ชาวบ้านร่วมใจกันสร้างองค์พระธาตุจำลองขึ้นบนริมฝั่งแม่น้ำโขงบริเวณใกล้เคียง เพื่อให้ประชาชนและนักท่องเที่ยวได้กราบไหว้สักการะบูชาได้ตลอดทั้งปี', 'https://maps.app.goo.gl/GwFNjDPB7fGY4uBZA', 'D01', 'PT01');
INSERT INTO place (place_id, place_name, description, gps, district_id, place_type_id) VALUES ('P002', 'ประติมากรรมพญานาค 2 องค์ ลานวัฒนธรรมริมโขงหน้าวัดลำดวน', 'เป็นแลนด์มาร์กสำคัญตั้งอยู่บริเวณลานกิจกรรมริมแม่น้ำโขง หน้าวัดลำดวน ตัวประติมากรรมเป็นรูปองค์พญานาคสีส้มทอง 2 องค์หันหน้าเข้าหากัน ลำตัวพ่นน้ำลงสู่แม่น้ำโขง

บริเวณนี้เป็นจุดชมทัศนียภาพแม่น้ำโขงที่สวยงาม นิยมมาเดินเล่น ชมพระอาทิตย์ตกดิน ถ่ายรูป และในตอนเย็นมักมีลานกิจกรรม ร้านค้า และตลาด walking street ในช่วงวันหยุด', 'https://maps.app.goo.gl/N2UsdXL5KsqSTn5s9', 'D01', 'PT01');
INSERT INTO place (place_id, place_name, description, gps, district_id, place_type_id) VALUES ('P003', 'ตลาดท่าเรือ หนองคาย', 'ตลาดการค้าชายแดนที่ตั้งอยู่ริมแม่น้ำโขง ใกล้กับจุดข้ามฝั่งและบริเวณตลาดท่าเทียบเรือ เป็นแหล่งรวมสินค้านำเข้า ของฝาก ของแต่งบ้าน เสื้อผ้า กระเป๋า เครื่องใช้ไฟฟ้า และอาหารแห้ง/อาหารสดจากประเทศเพื่อนบ้าน (ลาว, เวียดนาม, จีน)

เหมาะสำหรับการมาเดินเลือกซื้อของฝาก ทานอาหารพื้นเมืองริมโขง และสัมผัสบรรยากาศการค้าขายชายแดนริมฝั่งโขงของเมืองหนองคาย', 'https://maps.app.goo.gl/SY3b7sJUy8MHdTcQ7', 'D01', 'PT01');
INSERT INTO place (place_id, place_name, description, gps, district_id, place_type_id) VALUES ('P004', 'โรงแรมอัศวรรณ หนองคาย', 'ห้องพักกว้างขวาง สไตล์ทันสมัย มีลานจอดรถกว้างขวาง มีห้องประชุม/สัมมนา เหมาะสำหรับผู้ที่เดินทางมาติดต่อธุรกิจ นักท่องเที่ยว หรือผู้ที่ต้องการความสะดวกครบครันในจุดเดียว', 'https://maps.app.goo.gl/bZ6HKf9gXoohjsAb9', NULL, 'PT02');
INSERT INTO place (place_id, place_name, description, gps, district_id, place_type_id) VALUES ('P005', 'พบรักรีสอร์ท', 'ให้บริการห้องพักเป็นหลัง/บังกะโลเป็นส่วนตัว ราคาย่อมเยา มีพื้นที่จอดรถบริเวณหน้าห้องพัก สะดวกสบายสำหรับผู้ที่เดินทางด้วยรถยนต์ส่วนตัว', 'https://maps.app.goo.gl/ehpqBZiWLwJHZCBCA', NULL, 'PT02');
INSERT INTO place (place_id, place_name, description, gps, district_id, place_type_id) VALUES ('P006', 'ไทบ้านโอโซน หนองคาย', 'ตกแต่งเน้นความเรียบง่ายผสมผสานความเป็นท้องถิ่น ให้ความรู้สึกอบอุ่นเหมือนพักผ่อนอยู่บ้านสวน', 'https://maps.app.goo.gl/xVHyc2ipoHhNiiVNA', NULL, 'PT02');
INSERT INTO place (place_id, place_name, description, gps, district_id, place_type_id) VALUES ('P007', 'ปั๊มน้ำมัน ปตท.', 'ครบครันด้วยร้านค้าและบริการยอดนิยม เช่น ร้านกาแฟ Cafe Amazon, ร้านสะดวกซื้อ 7-Eleven, ศูนย์บริการยานยนต์ FIT Auto, ห้องน้ำสะอาดจำนวนมาก รวมถึงจุดชาร์จรถยนต์ไฟฟ้า (EV Station PluZ)', 'https://maps.app.goo.gl/y9UC7UKziCBxbv1DA', NULL, 'PT03');
INSERT INTO place (place_id, place_name, description, gps, district_id, place_type_id) VALUES ('P008', 'ปั๊มน้ำมัน Shell', 'มีร้านสะดวกซื้อ Shell Select, ร้านกาแฟ deli Café/Costa Coffee, จุดบริการถ่ายโอนน้ำมันเครื่อง เช็กสภาพรถเบื้องต้น และห้องน้ำสะอาดให้บริการ', 'https://maps.app.goo.gl/m2jirnKLeqpo1WjW6', NULL, 'PT03');
INSERT INTO place (place_id, place_name, description, gps, district_id, place_type_id) VALUES ('P009', 'ปั๊มน้ำมัน บางจาก', 'มีร้านกาแฟอินทนิล (Inthanin Coffee) ที่มีเมนูเครื่องดื่มและเบเกอรี่หลากหลาย, ร้านสะดวกซื้อ Mini Big C หรือร้านค้าในเครือ, จุดเติมลมยาง และห้องน้ำสะอาดรองรับผู้เดินทาง', 'https://maps.app.goo.gl/ww2xMqyQ4BM5rVSo9', NULL, 'PT03');
INSERT INTO place (place_id, place_name, description, gps, district_id, place_type_id) VALUES ('P010', 'ร้านจ๊ะจ๋า', 'เป็นศูนย์รวมสินค้าไลฟ์สไตล์และของใช้ในชีวิตประจำวัน มีให้เลือกหลากหลาย ทั้งของขวัญตามเทศกาล กิ๊ฟช็อปน่ารักๆ เครื่องเขียน อุปกรณ์ของเล่น และของใช้กระจุกกระจิก', 'https://maps.app.goo.gl/DQcZLYWnMyMzz7cX6', NULL, 'PT04');
INSERT INTO place (place_id, place_name, description, gps, district_id, place_type_id) VALUES ('P011', 'ต้อย อาหารทะเล', 'ร้านจำหน่ายและปรุงอาหารทะเลสดๆ มีวัตถุดิบหลากชนิด เช่น กุ้ง ปู ปลา หมึก หอย', 'https://maps.app.goo.gl/RXMUrcJyTxaXL5WF6', NULL, 'PT04');
INSERT INTO place (place_id, place_name, description, gps, district_id, place_type_id) VALUES ('P012', 'ร้านช้างรุ่งเรือง', 'ร้านค้าส่ง-ค้าปลีกจำหน่ายสินค้าทั่วไป สินค้าอุปโภคบริโภค เครื่องดื่ม และของใช้ในครัวเรือน', 'https://maps.app.goo.gl/HXMkVerHW3zEzdNV9', NULL, 'PT04');
INSERT INTO place (place_id, place_name, description, gps, district_id, place_type_id) VALUES ('P013', 'วัดโพธิ์ชัย', 'วัดคู่บ้านคู่เมืองหนองคาย และเป็นสถานที่ประดิษฐาน "หลวงพ่อพระใส" (พระพุทธรูปปางมารวิชัย ศิลปะล้านช้าง) ซึ่งเป็นพระพุทธรูปศักดิ์สิทธิ์ศูนย์รวมจิตใจของชาวหนองคายและชาวไทย-ลาว', 'https://maps.app.goo.gl/cW863EsLaVeFws1a8', 'D01', 'PT05');
INSERT INTO place (place_id, place_name, description, gps, district_id, place_type_id) VALUES ('P014', 'วัดพระธาตุบังพวน', 'เป็นโบราณสถานเก่าแก่และสถานที่ศักดิ์สิทธิ์สำคัญ โดยเป็นที่ประดิษฐาน "พระธาตุบังพวน" เจดีย์สถาปัตยกรรมล้านช้างที่บรรจุพระบรมสารีริกธาตุ', 'https://maps.app.goo.gl/5SjvTVGAre3GKx45A', NULL, 'PT05');
INSERT INTO place (place_id, place_name, description, gps, district_id, place_type_id) VALUES ('P015', 'วัดศรีชมภูองค์ตื้อ', 'สถานที่ประดิษฐาน "หลวงพ่อพระเจ้าองค์ตื้อ" พระพุทธรูปหล่อด้วยทองสำริดขนาดใหญ่ มีพุทธลักษณะงดงาม ศิลปะล้านช้างผสมผสานศิลปะเชียงแสน', 'https://maps.app.goo.gl/BywHMbk6t9qCx9c4A', 'D02', 'PT05');
INSERT INTO place (place_id, place_name, description, gps, district_id, place_type_id) VALUES ('P016', 'โรงพยาบาลหนองคาย', 'มีแพทย์เฉพาะทางหลากหลายสาขา ศูนย์ตรวจวินิจฉัยและรักษาครบวงจร รองรับผู้ป่วยอุบัติเหตุ-ฉุกเฉิน 24 ชั่วโมง และเป็นศูนย์รับส่งต่อผู้ป่วยหลักของจังหวัดหนองคาย', 'https://maps.app.goo.gl/urtzix3qFw5PrKkD6', 'D01', 'PT06');
INSERT INTO place (place_id, place_name, description, gps, district_id, place_type_id) VALUES ('P017', 'โรงพยาบาลหนองคายวัฒนา', 'ให้บริการทางการแพทย์ที่เน้นความสะดวกรวดเร็ว ห้องพักผู้ป่วยและสิ่งอำนวยความสะดวกทันสมัย รองรับการใช้สิทธิประกันสุขภาพเอกชน ประกันอุบัติเหตุ และผู้ป่วยทั่วไป', 'https://maps.app.goo.gl/tiU6QWr7ELghtnnJ7', NULL, 'PT06');
INSERT INTO place (place_id, place_name, description, gps, district_id, place_type_id) VALUES ('P018', 'โรงพยาบาลหนองคาย 2', 'ตั้งอยู่ที่ตำบลโพธิ์ชัย อำเภอเมืองหนองคาย เป็นสถานพยาบาลสาขาในเครือข่ายของโรงพยาบาลหนองคาย ให้บริการตรวจรักษาโรคเบื้องต้น', 'https://maps.app.goo.gl/2EUAZNtew5mydmVH9', 'D01', 'PT06');
INSERT INTO place (place_id, place_name, description, gps, district_id, place_type_id) VALUES ('P019', 'สถานีตำรวจภูธรเมืองหนองคาย', 'รับแจ้งความ คดีความ อุบัติเหตุ ร้องเรียน รับชำระค่าปรับ ทำบันทึกประจำวัน และดูแลความสงบเรียบร้อยรวมถึงการจราจรในพื้นที่อำเภอเมืองหนองคาย', 'https://maps.app.goo.gl/PibWifZUevrUSDXq6', 'D01', 'PT07');
INSERT INTO place (place_id, place_name, description, gps, district_id, place_type_id) VALUES ('P020', 'ตำรวจภูธรจังหวัดหนองคาย', 'เป็นหน่วยงานส่วนบัญชาการที่กำกับดูแลสถานีตำรวจภูธรทุกแห่งในเขต', 'https://maps.app.goo.gl/TR85T3yKFGgmCsu39', 'D01', 'PT07');
INSERT INTO place (place_id, place_name, description, gps, district_id, place_type_id) VALUES ('P021', 'สถานีตำรวจภูธรเวียงคุก', 'ดูแลความปลอดภัย สายตรวจ ปราบปราม และรับแจ้งความร้องทุกข์ของประชาชนในเขตตำบลเวียงคุกและพื้นที่ใกล้เคียง', 'https://maps.app.goo.gl/wDxq5gXpxsHvsFMAA', 'D01', 'PT07');

INSERT INTO place_image (place_image_id, link_file, place_id) VALUES ('PI0001', 'uploads/place/PI0001.webp', 'P001');
INSERT INTO place_image (place_image_id, link_file, place_id) VALUES ('PI0002', 'uploads/place/PI0002.webp', 'P002');
INSERT INTO place_image (place_image_id, link_file, place_id) VALUES ('PI0003', 'uploads/place/PI0003.webp', 'P003');
INSERT INTO place_image (place_image_id, link_file, place_id) VALUES ('PI0004', 'uploads/place/PI0004.webp', 'P004');
INSERT INTO place_image (place_image_id, link_file, place_id) VALUES ('PI0005', 'uploads/place/PI0005.webp', 'P005');
INSERT INTO place_image (place_image_id, link_file, place_id) VALUES ('PI0006', 'uploads/place/PI0006.jpg', 'P006');
INSERT INTO place_image (place_image_id, link_file, place_id) VALUES ('PI0007', 'uploads/place/PI0007.jpg', 'P007');
INSERT INTO place_image (place_image_id, link_file, place_id) VALUES ('PI0008', 'uploads/place/PI0008.jpg', 'P008');
INSERT INTO place_image (place_image_id, link_file, place_id) VALUES ('PI0009', 'uploads/place/PI0009.jpg', 'P009');
INSERT INTO place_image (place_image_id, link_file, place_id) VALUES ('PI0010', 'uploads/place/PI0010.jpg', 'P010');
INSERT INTO place_image (place_image_id, link_file, place_id) VALUES ('PI0011', 'uploads/place/PI0011.jpg', 'P011');
INSERT INTO place_image (place_image_id, link_file, place_id) VALUES ('PI0012', 'uploads/place/PI0012.jpg', 'P012');
INSERT INTO place_image (place_image_id, link_file, place_id) VALUES ('PI0013', 'uploads/place/PI0013.jpg', 'P013');
INSERT INTO place_image (place_image_id, link_file, place_id) VALUES ('PI0014', 'uploads/place/PI0014.jpg', 'P014');
INSERT INTO place_image (place_image_id, link_file, place_id) VALUES ('PI0015', 'uploads/place/PI0015.jpg', 'P015');
INSERT INTO place_image (place_image_id, link_file, place_id) VALUES ('PI0016', 'uploads/place/PI0016.jpg', 'P016');
INSERT INTO place_image (place_image_id, link_file, place_id) VALUES ('PI0017', 'uploads/place/PI0017.jpg', 'P017');
INSERT INTO place_image (place_image_id, link_file, place_id) VALUES ('PI0018', 'uploads/place/PI0018.jpg', 'P018');
INSERT INTO place_image (place_image_id, link_file, place_id) VALUES ('PI0019', 'uploads/place/PI0019.jpg', 'P019');
INSERT INTO place_image (place_image_id, link_file, place_id) VALUES ('PI0020', 'uploads/place/PI0020.jpg', 'P020');
INSERT INTO place_image (place_image_id, link_file, place_id) VALUES ('PI0021', 'uploads/place/PI0021.jpg', 'P021');

INSERT INTO community_product (product_id, product_name, description, place_id, product_type_id) VALUES ('C001', 'เครื่องจักสานบ้านหนองหมื่น', 'ผลิตภัณฑ์เครื่องจักสานบ้านหนองหมื่น ตั้งอยู่ที่หมู่ 5 ตำบลหนองกอมเกาะ อำเภอเมือง จังหวัดหนองคาย เป็นหนึ่งในแหล่งผลิตงานหัตถกรรมพื้นบ้านที่มีชื่อเสียงและเป็นเอกลักษณ์ของจังหวัด บริหารจัดการในรูปแบบ "กลุ่มสตรีสหกรณ์จักสานบ้านหนองหมื่น" เพื่อส่งเสริมอาชีพ เสริมสร้างรายได้ให้แก่คนในชุมชน และอนุรักษ์ภูมิปัญญาท้องถิ่นที่สืบทอดกันมาจากรุ่นสู่รุ่น

พิกัดผู้ผลิต: https://maps.app.goo.gl/cjmocU3Ky92cy4Py8', NULL, 'PR04');
INSERT INTO community_product (product_id, product_name, description, place_id, product_type_id) VALUES ('C002', 'หมูยอหนองคาย (แม่ถ้วน)', 'หมูยอแม่ถ้วน (ของฝากเมืองหนองคาย) เป็นหนึ่งในแบรนด์หมูยอและอาหารแปรรูปขึ้นชื่ออันดับต้นๆ ของจังหวัดหนองคาย ด้วยประสบการณ์ยาวนานกว่า 40 ปี ส่งต่อสูตรต้นตำรับอาหารเวียดนามอันทรงคุณค่า และได้รับการการันตีคุณภาพเป็นสินค้า OTOP 5 ดาวของจังหวัด

พิกัดผู้ผลิต: https://maps.app.goo.gl/LzyTMhSdUiN75mxE9', NULL, 'PR01');
INSERT INTO community_product (product_id, product_name, description, place_id, product_type_id) VALUES ('C003', 'ผ้าลายนาคใหญ่ หนองคาย', 'ผ้าลายนาคใหญ่ คือผ้าทอและลายผ้าเอกลักษณ์ประจำจังหวัดหนองคาย ซึ่งได้รับการออกแบบและจดลิขสิทธิ์เป็นลายผ้าอัตลักษณ์ของจังหวัด เพื่อสะท้อนถึงวิถีชีวิต ความเชื่อ ศรัทธา และความผูกพันของชาวหนองคายที่มีต่อพญานาคแห่งแม่น้ำโขง

พิกัดผู้ผลิต: https://maps.app.goo.gl/RGnV9Vq1ppLTCNNP8', NULL, 'PR03');

INSERT INTO product_image (product_image_id, link_file, product_id) VALUES ('CI0001', 'uploads/product/CI0001.jpg', 'C001');
INSERT INTO product_image (product_image_id, link_file, product_id) VALUES ('CI0002', 'uploads/product/CI0002.jpg', 'C002');
INSERT INTO product_image (product_image_id, link_file, product_id) VALUES ('CI0003', 'uploads/product/CI0003.jpg', 'C003');
