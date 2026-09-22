import 'package:equatable/equatable.dart';

class UserEntity extends Equatable {
  final int id;
  final String name;
  final String email;
  final String studentCode;
  final String career;
  final String role;

  const UserEntity({
    required this.id,
    required this.name,
    required this.email,
    required this.studentCode,
    required this.career,
    required this.role,
  });

  @override
  List<Object?> get props => [id, name, email, studentCode, career, role];
}
