use crate::error::Error;

pub fn print_block(logs: Vec<String>) {
    println!("====================================");
    for log in logs {
        println!("{}", log);
    }
    println!("====================================");
}

pub fn print_error(err: Error) {
    let mut logs = vec![
        format!("error found on {}", err.error_on),
        format!("error found while {}", err.error_while),
    ];
    logs.append(
        &mut err
            .error
            .iter()
            .map(|e| format!("{:?}", e.as_ref()))
            .collect::<Vec<String>>(),
    );

    print_block(logs);
}
