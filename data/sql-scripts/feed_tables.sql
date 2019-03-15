-- -----------------------------------------------------
-- inserts into `playlist`.`playlist`
-- -----------------------------------------------------
insert into `playlist`.`playlist` (name) values ("bestof_2018"),("bestof_2017");

-- -----------------------------------------------------
-- inserts into `playlist`.`video`
-- -----------------------------------------------------
insert into `playlist`.`video` (title, thumbnail) values ("video1","https://www.dailymotion.com/video/x73rgeq"),("video2","https://www.dailymotion.com/video/x744aty"),("video3","https://www.dailymotion.com/video/x7449hm"),("video4","https://www.dailymotion.com/video/x73mfpf?playlist=x6b57z"),("video5","https://www.dailymotion.com/video/x73mfpf?playlist=x6b58z");

-- -----------------------------------------------------
-- inserts into `playlist`.`playlist_video`
-- -----------------------------------------------------
insert into `playlist`.`playlist_video` (video_id,video_order,playlist_id) values (1,1,1),(2,2,1),(3,3,1),(4,4,1),(5,5,1),(3,1,2),(4,2,2);
