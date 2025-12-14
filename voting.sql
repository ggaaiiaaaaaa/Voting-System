SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

CREATE TABLE `audit_log` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `action` varchar(255) NOT NULL,
  `details` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `user_type` enum('student','teacher','admin') NOT NULL DEFAULT 'admin'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `candidates` (
  `id` int(11) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `student_id` int(11) UNSIGNED NOT NULL,
  `position_id` int(11) UNSIGNED NOT NULL,
  `is_winner` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


CREATE TABLE `elections` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `start_date` datetime DEFAULT NULL,
  `end_date` datetime DEFAULT NULL,
  `status` enum('Upcoming','Ongoing','Paused','Ended') DEFAULT 'Upcoming'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


CREATE TABLE `email_queue` (
  `id` int(11) UNSIGNED NOT NULL,
  `to_email` varchar(100) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `body` text NOT NULL,
  `status` enum('pending','sent','failed') DEFAULT 'pending',
  `attempts` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `sent_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `nominations` (
  `id` int(10) UNSIGNED NOT NULL,
  `nominator_id` int(11) DEFAULT NULL,
  `nominee_id` int(11) DEFAULT NULL,
  `student_id` int(10) UNSIGNED NOT NULL,
  `position_id` int(10) UNSIGNED NOT NULL,
  `status` enum('Pending','Approved','Rejected') NOT NULL DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `notifications` (
  `id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) NOT NULL,
  `user_type` enum('student','admin') NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `type` varchar(50) NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


CREATE TABLE `positions` (
  `id` int(11) UNSIGNED NOT NULL,
  `position_name` varchar(100) NOT NULL,
  `position_order` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `description` text DEFAULT NULL,
  `max_nominees` int(11) DEFAULT 1,
  `status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


INSERT INTO `positions` (`id`, `position_name`, `position_order`, `description`, `max_nominees`, `status`, `created_at`) VALUES
(38, 'Vice President', 3, NULL, 2, 'Active', '2025-12-14 11:15:40'),
(39, 'President', 2, NULL, 2, 'Active', '2025-12-14 11:15:45'),
(40, 'Auditor', 1, NULL, 2, 'Active', '2025-12-14 12:50:14');

CREATE TABLE `results` (
  `id` int(11) NOT NULL,
  `position_id` int(11) UNSIGNED NOT NULL,
  `candidate_id` int(11) NOT NULL,
  `votes` int(11) DEFAULT 0,
  `status` enum('Winner','Not Winner') DEFAULT 'Not Winner',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `students` (
  `id` int(11) UNSIGNED NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `fullname` varchar(255) NOT NULL,
  `student_id` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `grade_section` varchar(50) NOT NULL,
  `status` enum('Active','Inactive') DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `role` enum('admin','teacher','student') DEFAULT 'student',
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `votes` (
  `id` int(10) UNSIGNED NOT NULL,
  `voter_id` int(10) UNSIGNED NOT NULL,
  `position_id` int(10) UNSIGNED NOT NULL,
  `nomination_id` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `votes` (`id`, `voter_id`, `position_id`, `nomination_id`, `created_at`) VALUES
(132, 37, 39, 160, '2025-12-14 11:56:33'),
(133, 38, 39, 160, '2025-12-14 12:04:08'),
(134, 38, 38, 161, '2025-12-14 12:04:08'),
(135, 45, 39, 160, '2025-12-14 12:07:28'),
(136, 45, 38, 161, '2025-12-14 12:07:28');

ALTER TABLE `audit_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

ALTER TABLE `candidates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `position_id` (`position_id`);

ALTER TABLE `elections`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `email_queue`
  ADD PRIMARY KEY (`id`),
  ADD KEY `status` (`status`);

ALTER TABLE `nominations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_nomination` (`nominator_id`,`position_id`),
  ADD KEY `fk_nominations_student` (`student_id`),
  ADD KEY `fk_nominations_position` (`position_id`);

ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `user_type` (`user_type`),
  ADD KEY `is_read` (`is_read`);

ALTER TABLE `positions`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `results`
  ADD PRIMARY KEY (`id`),
  ADD KEY `position_id` (`position_id`),
  ADD KEY `candidate_id` (`candidate_id`);

ALTER TABLE `students`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `student_id` (`student_id`),
  ADD UNIQUE KEY `email` (`email`);

ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

ALTER TABLE `votes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_vote` (`voter_id`,`position_id`),
  ADD KEY `fk_vote_position` (`position_id`),
  ADD KEY `fk_vote_nomination` (`nomination_id`);

ALTER TABLE `audit_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=693;

ALTER TABLE `candidates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `elections`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

ALTER TABLE `email_queue`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `nominations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=162;

ALTER TABLE `notifications`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=207;

ALTER TABLE `positions`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

ALTER TABLE `results`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

ALTER TABLE `students`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

ALTER TABLE `votes`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=137;

ALTER TABLE `audit_log`
  ADD CONSTRAINT `audit_log_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

ALTER TABLE `candidates`
  ADD CONSTRAINT `fk_candidates_position` FOREIGN KEY (`position_id`) REFERENCES `positions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_candidates_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `nominations`
  ADD CONSTRAINT `fk_nominations_position` FOREIGN KEY (`position_id`) REFERENCES `positions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_nominations_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `results`
  ADD CONSTRAINT `fk_results_position` FOREIGN KEY (`position_id`) REFERENCES `positions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `votes`
  ADD CONSTRAINT `fk_vote_nomination` FOREIGN KEY (`nomination_id`) REFERENCES `nominations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_vote_position` FOREIGN KEY (`position_id`) REFERENCES `positions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_votes_voter` FOREIGN KEY (`voter_id`) REFERENCES `students` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;
