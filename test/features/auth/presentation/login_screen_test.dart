import 'package:campus_connect/features/auth/domain/entities/user_entity.dart';
import 'package:campus_connect/features/auth/presentation/providers/auth_provider.dart';
import 'package:campus_connect/features/auth/presentation/screens/login_screen.dart';
import 'package:campus_connect/features/requests/presentation/providers/request_provider.dart';
import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:mocktail/mocktail.dart';
import 'package:provider/provider.dart';

class MockAuthProvider extends Mock implements AuthProvider {}
class MockRequestProvider extends Mock implements RequestProvider {}

void main() {
  late MockAuthProvider mockAuthProvider;
  late MockRequestProvider mockRequestProvider;

  setUp(() {
    mockAuthProvider = MockAuthProvider();
    mockRequestProvider = MockRequestProvider();

    when(() => mockAuthProvider.isLoading).thenReturn(false);
    when(() => mockAuthProvider.status).thenReturn(AuthStatus.unauthenticated);
    when(() => mockAuthProvider.errorMessage).thenReturn(null);
    when(() => mockAuthProvider.currentUser).thenReturn(
      const UserEntity(
        id: 1,
        name: 'Estudiante Prueba',
        email: 'estudiante@universidad.edu',
        studentCode: 'U12345',
        career: 'Ingeniería',
        role: 'student',
      ),
    );

    when(() => mockRequestProvider.isLoading).thenReturn(false);
    when(() => mockRequestProvider.errorMessage).thenReturn(null);
    when(() => mockRequestProvider.requests).thenReturn([]);
    when(() => mockRequestProvider.currentFilter).thenReturn('Todos');
    when(() => mockRequestProvider.fetchRequests()).thenAnswer((_) async {});
  });

  Widget buildTestableWidget() {
    return MultiProvider(
      providers: [
        ChangeNotifierProvider<AuthProvider>.value(value: mockAuthProvider),
        ChangeNotifierProvider<RequestProvider>.value(value: mockRequestProvider),
      ],
      child: const MaterialApp(
        home: LoginScreen(),
      ),
    );
  }

  testWidgets('Valida campos requeridos en pantalla de login', (WidgetTester tester) async {
    await tester.pumpWidget(buildTestableWidget());

    expect(find.text('CAMPUS CONNECT'), findsOneWidget);
    expect(find.byKey(const Key('login_email_input')), findsOneWidget);
    expect(find.byKey(const Key('login_password_input')), findsOneWidget);

    final submitBtn = find.byKey(const Key('login_submit_btn'));
    await tester.tap(submitBtn);
    await tester.pumpAndSettle();

    expect(find.text('Ingrese su correo institucional'), findsOneWidget);
    expect(find.text('Ingrese su contraseña'), findsOneWidget);
    verifyNever(() => mockAuthProvider.login(any(), any()));
  });

  testWidgets('Ejecuta login cuando las credenciales son válidas', (WidgetTester tester) async {
    when(() => mockAuthProvider.login(any(), any())).thenAnswer((_) async => true);

    await tester.pumpWidget(buildTestableWidget());

    await tester.enterText(
      find.byKey(const Key('login_email_input')),
      'estudiante@universidad.edu',
    );
    await tester.enterText(
      find.byKey(const Key('login_password_input')),
      'password123',
    );

    final submitBtn = find.byKey(const Key('login_submit_btn'));
    await tester.tap(submitBtn);
    await tester.pumpAndSettle();

    verify(() => mockAuthProvider.login('estudiante@universidad.edu', 'password123')).called(1);
  });
}
