import 'package:campus_connect/features/auth/domain/entities/user_entity.dart';
import 'package:campus_connect/features/auth/presentation/providers/auth_provider.dart';
import 'package:campus_connect/features/requests/domain/entities/request_entity.dart';
import 'package:campus_connect/features/requests/presentation/providers/request_provider.dart';
import 'package:campus_connect/features/requests/presentation/screens/request_list_screen.dart';
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

    when(() => mockAuthProvider.currentUser).thenReturn(
      const UserEntity(
        id: 1,
        name: 'Carlos Mendoza',
        email: 'carlos.mendoza@universidad.edu',
        studentCode: 'U20210045',
        career: 'Ingeniería de Sistemas',
        role: 'student',
      ),
    );

    when(() => mockRequestProvider.isLoading).thenReturn(false);
    when(() => mockRequestProvider.errorMessage).thenReturn(null);
    when(() => mockRequestProvider.currentFilter).thenReturn('Todos');
    when(() => mockRequestProvider.fetchRequests()).thenAnswer((_) async {});
  });

  testWidgets('Muestra lista de solicitudes y chips de filtrado correctamente', (WidgetTester tester) async {
    final tRequests = [
      const RequestEntity(
        id: 1,
        requestType: 'Mantenimiento',
        title: 'Luminaria quemada en Aula 101',
        location: 'Pabellón A',
        description: 'Parpadeo constante de fluorescente',
        priority: 'Media',
        status: 'Pendiente',
        createdAt: '2026-09-22T09:00:00Z',
      ),
    ];

    when(() => mockRequestProvider.requests).thenReturn(tRequests);

    await tester.pumpWidget(
      MultiProvider(
        providers: [
          ChangeNotifierProvider<AuthProvider>.value(value: mockAuthProvider),
          ChangeNotifierProvider<RequestProvider>.value(value: mockRequestProvider),
        ],
        child: const MaterialApp(
          home: RequestListScreen(),
        ),
      ),
    );

    await tester.pumpAndSettle();

    expect(find.text('Mis Solicitudes'), findsOneWidget);
    expect(find.text('Hola, Carlos Mendoza'), findsOneWidget);
    expect(find.text('Luminaria quemada en Aula 101'), findsOneWidget);
    expect(find.byKey(const Key('filter_chip_Pendiente')), findsOneWidget);
    expect(find.byKey(const Key('fab_create_request')), findsOneWidget);
  });
}
